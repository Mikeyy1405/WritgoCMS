<?php
/**
 * Plugin Name: Writgo Integrated Writer
 * Description: Genereer volledige SEO artikelen direct binnen de 'Nieuw Bericht' editor.
 * Version: 3.0
 * Author: Mikeyy1405
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ==========================================================================
// 1. CONFIGURATIE
// ==========================================================================
define('WRITGO_API_KEY', 'sk-proj-...'); // <--- PLAK HIER JE OPENAI KEY


// ==========================================================================
// 2. VOEG HET "AI BEDIENINGSPANEEL" TOE AAN DE EDITOR
// ==========================================================================
function writgo_add_editor_metabox() {
    // Voeg toe aan 'post' (berichten)
    add_meta_box(
        'writgo_generator_box',      // ID
        '✨ Writgo AI Content Generator', // Titel
        'writgo_render_editor_ui',   // Callback functie
        'post',                      // Post type
        'normal',                    // Positie (onder de editor)
        'high'                       // Prioriteit (helemaal bovenaan)
    );
}
add_action( 'add_meta_boxes', 'writgo_add_editor_metabox' );

function writgo_render_editor_ui( $post ) {
    // Haal opgeslagen waarden op (als je het bericht eerder hebt opgeslagen)
    $brand = get_post_meta($post->ID, '_wg_brand', true) ?: 'JouwMerk.nl';
    $topic = get_post_meta($post->ID, '_wg_topic', true);
    $keyword = get_post_meta($post->ID, '_wg_keyword', true);
    $products = get_post_meta($post->ID, '_wg_products', true);
    ?>
    
    <div class="writgo-panel" style="padding: 15px; background: #f9f9f9; border-top: 1px solid #eee;">
        <p style="margin-top:0; color:#666;"><em>Vul de velden in en laat AI de inhoud van dit bericht overschrijven met een geoptimaliseerd artikel.</em></p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- LINKER KOLOM -->
            <div>
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Merknaam</label>
                <input type="text" id="wg_brand" value="<?php echo esc_attr($brand); ?>" style="width:100%; padding:8px;" placeholder="Gigadier.nl">
                
                <br><br>

                <label style="font-weight:bold; display:block; margin-bottom:5px;">Onderwerp</label>
                <input type="text" id="wg_topic" value="<?php echo esc_attr($topic); ?>" style="width:100%; padding:8px;" placeholder="Graanvrij Hondenvoer">
            </div>

            <!-- RECHTER KOLOM -->
            <div>
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Focus Keyword (SEO)</label>
                <input type="text" id="wg_keyword" value="<?php echo esc_attr($keyword); ?>" style="width:100%; padding:8px;" placeholder="Beste hondenvoer">
                
                <br><br>

                <label style="font-weight:bold; display:block; margin-bottom:5px;">Doelgroep</label>
                <input type="text" id="wg_audience" style="width:100%; padding:8px;" placeholder="Hondeneigenaren met budget">
            </div>
        </div>

        <br>
        
        <label style="font-weight:bold; display:block; margin-bottom:5px;">Product Data (Plak de 5 producten hier)</label>
        <textarea id="wg_products" style="width:100%; height:150px; padding:10px; font-family:monospace; border:1px solid #ccc;"><?php echo esc_textarea($products); ?></textarea>

        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;">

        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div style="color: #d63638;">
                ⚠️ <strong>Let op:</strong> Dit overschrijft de huidige tekst in de editor!
            </div>
            <div>
                <span id="wg-spinner" style="display:none; margin-right:10px; font-weight:bold; color:#2271b1;">
                    <span class="dashicons dashicons-update spin"></span> AI is aan het schrijven... even geduld...
                </span>
                <button type="button" id="wg-generate-btn" class="button button-primary button-hero">
                    Genereer Artikel in Editor
                </button>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#wg-generate-btn').on('click', function() {
            var postId = <?php echo $post->ID; ?>;
            
            // Check of post al is opgeslagen (ID nodig)
            if(postId === 0) {
                alert('Sla het bericht eerst op als concept (bovenaan rechts) voordat je genereert.');
                return;
            }

            if(!confirm('Weet je zeker dat je de inhoud wilt genereren? Huidige tekst wordt overschreven.')) return;

            // UI Feedback
            var btn = $(this);
            btn.attr('disabled', true);
            $('#wg-spinner').show();

            // Data verzamelen
            var data = {
                action: 'writgo_generate_integrated',
                post_id: postId,
                brand: $('#wg_brand').val(),
                topic: $('#wg_topic').val(),
                keyword: $('#wg_keyword').val(),
                audience: $('#wg_audience').val(),
                products: $('#wg_products').val()
            };

            // AJAX Call
            $.post(ajaxurl, data, function(response) {
                if(response.success) {
                    // HERLAAD DE PAGINA om de nieuwe content te tonen
                    window.location.reload(); 
                } else {
                    alert('Fout: ' + response.data);
                    btn.attr('disabled', false);
                    $('#wg-spinner').hide();
                }
            });
        });
    });
    </script>
    <?php
}


// ==========================================================================
// 3. DE GENERATIE LOGICA (SERVER SIDE)
// ==========================================================================
add_action('wp_ajax_writgo_generate_integrated', 'writgo_handle_generation_integrated');

function writgo_handle_generation_integrated() {
    // 1. Inputs valideren
    $post_id = intval($_POST['post_id']);
    $brand = sanitize_text_field($_POST['brand']);
    $topic = sanitize_text_field($_POST['topic']);
    $keyword = sanitize_text_field($_POST['keyword']);
    $products = sanitize_textarea_field($_POST['products']);
    $audience = sanitize_text_field($_POST['audience']);

    if(!$post_id) wp_send_json_error('Geen post ID gevonden.');

    // Optioneel: Sla de inputs op in meta, zodat ze er nog staan na refresh
    update_post_meta($post_id, '_wg_brand', $brand);
    update_post_meta($post_id, '_wg_topic', $topic);
    update_post_meta($post_id, '_wg_keyword', $keyword);
    update_post_meta($post_id, '_wg_products', $products);

    // 2. Prompt Bouwen (Compacte versie van de Master Prompt)
    $prompt = <<<EOT
Jij bent Hoofdredacteur van $brand. Schrijf een SEO artikel over '$topic'.
Doelgroep: $audience.
Keyword: '$keyword'.

CRUCIALE REGELS:
- Geen inleiding als "Hier is je artikel". Begin direct met de H1.
- Gebruik Markdown (H1, H2, H3, Tabellen).
- Gebruik de DATA hieronder voor een vergelijkingstabel en reviews:
---
$products
---
Structuur:
1. H1 Titel
2. Intro
3. Waar op letten?
4. Vergelijkingstabel (Markdown)
5. Top 5 Reviews
6. Conclusie
EOT;

    // 3. Call OpenAI
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'timeout' => 120,
        'headers' => [
            'Authorization' => 'Bearer ' . WRITGO_API_KEY,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'Je bent een expert copywriter.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ])
    ]);

    if (is_wp_error($response)) wp_send_json_error('OpenAI Error: ' . $response->get_error_message());
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    $content = $body['choices'][0]['message']['content'] ?? '';

    if(empty($content)) wp_send_json_error('Geen content ontvangen.');

    // 4. UPDATE HET HUIDIGE BERICHT
    
    // Probeer een titel te extraheren als die er nog niet is
    $post_title = get_the_title($post_id);
    if($post_title == 'Auto Draft' || empty($post_title)) {
        if (preg_match('/^#\s+(.*)$/m', $content, $matches)) {
            $post_title = trim($matches[1]);
            // Verwijder H1 uit body om dubbele titels te voorkomen
            $content = str_replace($matches[0], '', $content);
        }
    }

    $updated = wp_update_post([
        'ID' => $post_id,
        'post_title' => $post_title,
        'post_content' => $content,
        'post_status' => 'draft' // Laat het op concept staan voor veiligheid
    ]);

    if($updated) {
        wp_send_json_success('Content geupdate!');
    } else {
        wp_send_json_error('Kon bericht niet updaten in database.');
    }
}
