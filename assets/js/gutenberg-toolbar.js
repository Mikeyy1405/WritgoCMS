/**
 * Gutenberg AI Toolbar
 *
 * Adds AI-powered block toolbar and sidebar panel to Gutenberg.
 *
 * @package WritgoCMS
 */

(function(wp) {
    'use strict';

    var registerPlugin = wp.plugins.registerPlugin;
    var PluginSidebar = wp.editPost.PluginSidebar;
    var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
    var createElement = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var useState = wp.element.useState;
    var useEffect = wp.element.useEffect;
    var useSelect = wp.data.useSelect;
    var useDispatch = wp.data.useDispatch;
    var Button = wp.components.Button;
    var PanelBody = wp.components.PanelBody;
    var PanelRow = wp.components.PanelRow;
    var TextareaControl = wp.components.TextareaControl;
    var Spinner = wp.components.Spinner;
    var Notice = wp.components.Notice;
    var Modal = wp.components.Modal;
    var ToolbarGroup = wp.blockEditor.BlockControls ? null : null;
    var BlockControls = wp.blockEditor.BlockControls;
    var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
    var addFilter = wp.hooks.addFilter;
    var DropdownMenu = wp.components.DropdownMenu;
    var MenuGroup = wp.components.MenuGroup;
    var MenuItem = wp.components.MenuItem;
    var __ = wp.i18n.__;

    var settings = window.writgocmsAiToolbar || {};
    var i18n = settings.i18n || {};

    /**
     * AI Sidebar Panel Component
     */
    function WritgoAISidebar() {
        var _useState = useState(false),
            isLoading = _useState[0],
            setIsLoading = _useState[1];

        var _useState2 = useState(null),
            error = _useState2[0],
            setError = _useState2[1];

        var _useState3 = useState(null),
            success = _useState3[0],
            setSuccess = _useState3[1];

        var _useState4 = useState(null),
            result = _useState4[0],
            setResult = _useState4[1];

        var _useState5 = useState(false),
            showModal = _useState5[0],
            setShowModal = _useState5[1];

        var _useState6 = useState(''),
            modalAction = _useState6[0],
            setModalAction = _useState6[1];

        var _useState7 = useState(''),
            imagePrompt = _useState7[0],
            setImagePrompt = _useState7[1];

        var _useState8 = useState({
            requests_used: settings.usage ? settings.usage.requests_used : 0,
            requests_remaining: settings.usage ? settings.usage.requests_remaining : 1000,
            daily_limit: settings.usage ? settings.usage.daily_limit : 1000
        }),
            usage = _useState8[0],
            setUsage = _useState8[1];

        // Get post data
        var postData = useSelect(function(select) {
            var editor = select('core/editor');
            return {
                title: editor.getEditedPostAttribute('title'),
                content: editor.getEditedPostContent(),
                blocks: select('core/block-editor').getBlocks()
            };
        }, []);

        var _useDispatch = useDispatch('core/editor'),
            editPost = _useDispatch.editPost;

        var _useDispatch2 = useDispatch('core/block-editor'),
            updateBlockAttributes = _useDispatch2.updateBlockAttributes,
            replaceBlocks = _useDispatch2.replaceBlocks,
            insertBlocks = _useDispatch2.insertBlocks;

        // Update usage when events fire
        useEffect(function() {
            var handleUsageUpdate = function(e, usageData) {
                setUsage(usageData);
            };

            jQuery(document).on('writgoai:usage:updated', handleUsageUpdate);

            return function() {
                jQuery(document).off('writgoai:usage:updated', handleUsageUpdate);
            };
        }, []);

        /**
         * Handle AI action
         */
        function handleAction(action) {
            if (isLoading) return;

            var client = window.WritgoAIClient;
            if (!client) {
                setError('AI client not available');
                return;
            }

            setIsLoading(true);
            setError(null);
            setSuccess(null);
            setResult(null);

            var promise;
            var plainContent = getPlainContent(postData.content);

            switch (action) {
                case 'rewriteArticle':
                    promise = client.rewriteArticle(plainContent, postData.title);
                    break;
                case 'seoOptimize':
                    promise = client.seoOptimize(plainContent, postData.title);
                    break;
                case 'generateMeta':
                    promise = client.generateMeta(plainContent, postData.title);
                    break;
                case 'generateFeatured':
                    setModalAction('generateFeatured');
                    setShowModal(true);
                    setIsLoading(false);
                    return;
                case 'autoLinkContent':
                    promise = client.addLinks(plainContent);
                    break;
                default:
                    setIsLoading(false);
                    return;
            }

            promise
                .then(function(response) {
                    setIsLoading(false);

                    if (action === 'rewriteArticle') {
                        setResult({
                            type: 'rewrite',
                            content: response.content
                        });
                        setSuccess(i18n.success || 'Artikel herschreven!');
                    } else if (action === 'seoOptimize') {
                        setResult({
                            type: 'seo',
                            data: response.seo_data || response.raw
                        });
                        setSuccess('SEO analyse gereed!');
                    } else if (action === 'generateMeta') {
                        setResult({
                            type: 'meta',
                            content: response.meta_description
                        });
                        setSuccess('Meta description gegenereerd!');
                    } else if (action === 'autoLinkContent') {
                        setResult({
                            type: 'links',
                            content: response.content
                        });
                        setSuccess('Links toegevoegd!');
                    }

                    // Update usage
                    if (response.usage) {
                        setUsage(response.usage);
                    }
                })
                .catch(function(err) {
                    setIsLoading(false);
                    setError(err.message || 'Er is een fout opgetreden');
                });
        }

        /**
         * Handle image generation
         */
        function handleGenerateImage() {
            if (!imagePrompt.trim()) {
                setError('Voer een prompt in voor de afbeelding');
                return;
            }

            var client = window.WritgoAIClient;
            if (!client) {
                setError('AI client not available');
                return;
            }

            setIsLoading(true);
            setError(null);

            client.generateImage(imagePrompt)
                .then(function(response) {
                    setIsLoading(false);
                    setShowModal(false);

                    if (response.image_url) {
                        // Set as featured image if attachment_id is available
                        if (response.attachment_id) {
                            editPost({ featured_media: response.attachment_id });
                            setSuccess('Featured image ingesteld!');
                        } else {
                            // Insert as image block
                            var imageBlock = wp.blocks.createBlock('core/image', {
                                url: response.image_url,
                                alt: imagePrompt
                            });
                            insertBlocks(imageBlock);
                            setSuccess('Afbeelding ingevoegd!');
                        }
                    }

                    setImagePrompt('');
                })
                .catch(function(err) {
                    setIsLoading(false);
                    setError(err.message || 'Afbeelding genereren mislukt');
                });
        }

        /**
         * Get plain text content from blocks
         */
        function getPlainContent(content) {
            // Remove HTML tags for AI processing
            var temp = document.createElement('div');
            temp.innerHTML = content;
            return temp.textContent || temp.innerText || '';
        }

        /**
         * Apply rewritten content
         */
        function applyRewrittenContent() {
            if (!result || result.type !== 'rewrite') return;

            // Convert the new content to blocks
            var newBlocks = wp.blocks.parse('<!-- wp:paragraph -->\n<p>' + result.content.replace(/\n\n/g, '</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>').replace(/\n/g, '<br>') + '</p>\n<!-- /wp:paragraph -->');

            if (newBlocks && newBlocks.length > 0) {
                // Replace all content blocks
                var currentBlocks = postData.blocks;
                var blockIds = currentBlocks.map(function(block) {
                    return block.clientId;
                });

                if (blockIds.length > 0) {
                    replaceBlocks(blockIds, newBlocks);
                }
            }

            setResult(null);
            setSuccess('Content toegepast!');
        }

        return createElement(
            Fragment,
            null,
            createElement(
                PluginSidebarMoreMenuItem,
                {
                    target: 'writgoai-sidebar',
                    icon: 'superhero'
                },
                i18n.sidebarTitle || 'WritgoAI'
            ),
            createElement(
                PluginSidebar,
                {
                    name: 'writgoai-sidebar',
                    title: i18n.sidebarTitle || 'WritgoAI',
                    icon: 'superhero'
                },
                createElement(
                    PanelBody,
                    { title: 'AI Acties', initialOpen: true },
                    // Error notice
                    error && createElement(
                        Notice,
                        {
                            status: 'error',
                            isDismissible: true,
                            onRemove: function() { setError(null); }
                        },
                        error
                    ),
                    // Success notice
                    success && createElement(
                        Notice,
                        {
                            status: 'success',
                            isDismissible: true,
                            onRemove: function() { setSuccess(null); }
                        },
                        success
                    ),
                    // Action buttons
                    createElement(
                        PanelRow,
                        null,
                        createElement(
                            Button,
                            {
                                variant: 'secondary',
                                onClick: function() { handleAction('rewriteArticle'); },
                                disabled: isLoading,
                                className: 'writgoai-sidebar-btn',
                                icon: 'edit'
                            },
                            isLoading ? createElement(Spinner, null) : null,
                            ' ',
                            i18n.rewriteArticle || 'Artikel herschrijven'
                        )
                    ),
                    createElement(
                        PanelRow,
                        null,
                        createElement(
                            Button,
                            {
                                variant: 'secondary',
                                onClick: function() { handleAction('seoOptimize'); },
                                disabled: isLoading,
                                className: 'writgoai-sidebar-btn',
                                icon: 'search'
                            },
                            i18n.seoOptimize || 'SEO optimaliseren'
                        )
                    ),
                    createElement(
                        PanelRow,
                        null,
                        createElement(
                            Button,
                            {
                                variant: 'secondary',
                                onClick: function() { handleAction('generateMeta'); },
                                disabled: isLoading,
                                className: 'writgoai-sidebar-btn',
                                icon: 'text'
                            },
                            i18n.generateMeta || 'Meta description'
                        )
                    ),
                    createElement(
                        PanelRow,
                        null,
                        createElement(
                            Button,
                            {
                                variant: 'secondary',
                                onClick: function() { handleAction('generateFeatured'); },
                                disabled: isLoading,
                                className: 'writgoai-sidebar-btn',
                                icon: 'format-image'
                            },
                            i18n.generateFeatured || 'Featured image'
                        )
                    ),
                    createElement(
                        PanelRow,
                        null,
                        createElement(
                            Button,
                            {
                                variant: 'secondary',
                                onClick: function() { handleAction('autoLinkContent'); },
                                disabled: isLoading,
                                className: 'writgoai-sidebar-btn',
                                icon: 'admin-links'
                            },
                            i18n.autoLinkContent || 'Auto-link content'
                        )
                    )
                ),
                // Result panel
                result && createElement(
                    PanelBody,
                    { title: 'Resultaat', initialOpen: true },
                    result.type === 'rewrite' && createElement(
                        Fragment,
                        null,
                        createElement(
                            'div',
                            { className: 'writgoai-result-preview' },
                            result.content.substring(0, 500) + (result.content.length > 500 ? '...' : '')
                        ),
                        createElement(
                            PanelRow,
                            null,
                            createElement(
                                Button,
                                {
                                    variant: 'primary',
                                    onClick: applyRewrittenContent
                                },
                                'Toepassen'
                            ),
                            createElement(
                                Button,
                                {
                                    variant: 'secondary',
                                    onClick: function() { setResult(null); },
                                    style: { marginLeft: '8px' }
                                },
                                'Annuleren'
                            )
                        )
                    ),
                    result.type === 'meta' && createElement(
                        Fragment,
                        null,
                        createElement(
                            'div',
                            { className: 'writgoai-result-preview writgoai-meta-result' },
                            createElement('strong', null, 'Meta description:'),
                            createElement('p', null, result.content)
                        ),
                        createElement(
                            PanelRow,
                            null,
                            createElement(
                                Button,
                                {
                                    variant: 'secondary',
                                    onClick: function() {
                                        // Copy to clipboard
                                        navigator.clipboard.writeText(result.content);
                                        setSuccess('Gekopieerd naar klembord!');
                                    }
                                },
                                'Kopiëren'
                            )
                        )
                    ),
                    result.type === 'seo' && createElement(
                        'div',
                        { className: 'writgoai-seo-result' },
                        typeof result.data === 'object' ? createElement(
                            Fragment,
                            null,
                            result.data.optimized_title && createElement(
                                'div',
                                { className: 'seo-item' },
                                createElement('strong', null, 'Geoptimaliseerde titel:'),
                                createElement('p', null, result.data.optimized_title)
                            ),
                            result.data.meta_description && createElement(
                                'div',
                                { className: 'seo-item' },
                                createElement('strong', null, 'Meta description:'),
                                createElement('p', null, result.data.meta_description)
                            ),
                            result.data.improvements && createElement(
                                'div',
                                { className: 'seo-item' },
                                createElement('strong', null, 'Verbeterpunten:'),
                                createElement(
                                    'ul',
                                    null,
                                    result.data.improvements.map(function(item, idx) {
                                        return createElement('li', { key: idx }, item);
                                    })
                                )
                            ),
                            result.data.seo_score && createElement(
                                'div',
                                { className: 'seo-score' },
                                'SEO Score: ',
                                createElement('strong', null, result.data.seo_score + '/100')
                            )
                        ) : createElement('pre', null, result.data)
                    )
                ),
                // Usage panel
                createElement(
                    PanelBody,
                    { title: i18n.usageLabel || 'Gebruik', initialOpen: false },
                    createElement(
                        'div',
                        { className: 'writgoai-usage' },
                        createElement(
                            'div',
                            { className: 'usage-bar' },
                            createElement(
                                'div',
                                {
                                    className: 'usage-fill',
                                    style: {
                                        width: Math.min(100, (usage.requests_used / usage.daily_limit) * 100) + '%'
                                    }
                                }
                            )
                        ),
                        createElement(
                            'div',
                            { className: 'usage-text' },
                            usage.requests_used + ' / ' + usage.daily_limit + ' ' + (i18n.requestsRemaining || 'verzoeken gebruikt')
                        )
                    )
                )
            ),
            // Image generation modal
            showModal && modalAction === 'generateFeatured' && createElement(
                Modal,
                {
                    title: i18n.generateFeatured || 'Featured image genereren',
                    onRequestClose: function() {
                        setShowModal(false);
                        setImagePrompt('');
                    }
                },
                createElement(
                    TextareaControl,
                    {
                        label: i18n.imagePrompt || 'Beschrijf de afbeelding',
                        value: imagePrompt,
                        onChange: setImagePrompt,
                        rows: 3
                    }
                ),
                createElement(
                    'div',
                    { className: 'writgoai-modal-actions' },
                    createElement(
                        Button,
                        {
                            variant: 'primary',
                            onClick: handleGenerateImage,
                            disabled: isLoading || !imagePrompt.trim()
                        },
                        isLoading ? createElement(Spinner, null) : null,
                        ' ',
                        i18n.generateBtn || 'Genereren'
                    ),
                    createElement(
                        Button,
                        {
                            variant: 'secondary',
                            onClick: function() {
                                setShowModal(false);
                                setImagePrompt('');
                            },
                            style: { marginLeft: '8px' }
                        },
                        i18n.cancel || 'Annuleren'
                    )
                )
            )
        );
    }

    /**
     * Block Toolbar Extension
     * Adds AI actions to block toolbar
     */
    var withWritgoAIToolbar = createHigherOrderComponent(function(BlockEdit) {
        return function(props) {
            var blockName = props.name;
            var clientId = props.clientId;

            var _useState9 = useState(false),
                isLoading = _useState9[0],
                setIsLoading = _useState9[1];

            var _useState10 = useState(false),
                showImageModal = _useState10[0],
                setShowImageModal = _useState10[1];

            var _useState11 = useState(''),
                imagePrompt = _useState11[0],
                setImagePrompt = _useState11[1];

            var _useDispatch3 = useDispatch('core/block-editor'),
                updateBlockAttributes = _useDispatch3.updateBlockAttributes,
                insertBlocks = _useDispatch3.insertBlocks;

            // Only show for text blocks
            var supportedBlocks = [
                'core/paragraph',
                'core/heading',
                'core/list',
                'core/quote'
            ];

            if (supportedBlocks.indexOf(blockName) === -1) {
                return createElement(BlockEdit, props);
            }

            /**
             * Handle block rewrite
             */
            function handleRewriteBlock() {
                var content = props.attributes.content || '';
                if (!content) return;

                var client = window.WritgoAIClient;
                if (!client) return;

                setIsLoading(true);

                client.rewriteBlock(content)
                    .then(function(response) {
                        setIsLoading(false);
                        updateBlockAttributes(clientId, { content: response.content });
                    })
                    .catch(function(err) {
                        setIsLoading(false);
                        console.error('Rewrite error:', err);
                    });
            }

            /**
             * Handle image generation for block
             */
            function handleGenerateImage() {
                if (!imagePrompt.trim()) return;

                var client = window.WritgoAIClient;
                if (!client) return;

                setIsLoading(true);

                client.generateImage(imagePrompt)
                    .then(function(response) {
                        setIsLoading(false);
                        setShowImageModal(false);

                        if (response.image_url) {
                            // Insert image block after current block
                            var imageBlock = wp.blocks.createBlock('core/image', {
                                url: response.image_url,
                                alt: imagePrompt
                            });

                            var index = wp.data.select('core/block-editor').getBlockIndex(clientId);
                            insertBlocks(imageBlock, index + 1);
                        }

                        setImagePrompt('');
                    })
                    .catch(function(err) {
                        setIsLoading(false);
                        console.error('Image generation error:', err);
                    });
            }

            /**
             * Handle auto-linking
             */
            function handleAutoLink() {
                var content = props.attributes.content || '';
                if (!content) return;

                var client = window.WritgoAIClient;
                if (!client) return;

                setIsLoading(true);

                client.addLinks(content)
                    .then(function(response) {
                        setIsLoading(false);
                        updateBlockAttributes(clientId, { content: response.content });
                    })
                    .catch(function(err) {
                        setIsLoading(false);
                        console.error('Auto-link error:', err);
                    });
            }

            return createElement(
                Fragment,
                null,
                createElement(BlockEdit, props),
                createElement(
                    BlockControls,
                    { group: 'other' },
                    createElement(
                        DropdownMenu,
                        {
                            icon: 'superhero',
                            label: i18n.aiActions || 'WritgoAI',
                            className: 'writgoai-block-dropdown'
                        },
                        function(_ref) {
                            var onClose = _ref.onClose;
                            return createElement(
                                Fragment,
                                null,
                                createElement(
                                    MenuGroup,
                                    null,
                                    createElement(
                                        MenuItem,
                                        {
                                            icon: 'edit',
                                            onClick: function() {
                                                onClose();
                                                handleRewriteBlock();
                                            },
                                            disabled: isLoading
                                        },
                                        isLoading ? createElement(Spinner, null) : null,
                                        ' ',
                                        i18n.rewriteBlock || 'Blok herschrijven'
                                    ),
                                    createElement(
                                        MenuItem,
                                        {
                                            icon: 'format-image',
                                            onClick: function() {
                                                onClose();
                                                setShowImageModal(true);
                                            }
                                        },
                                        i18n.generateImage || 'Afbeelding genereren'
                                    ),
                                    createElement(
                                        MenuItem,
                                        {
                                            icon: 'admin-links',
                                            onClick: function() {
                                                onClose();
                                                handleAutoLink();
                                            },
                                            disabled: isLoading
                                        },
                                        i18n.autoLink || 'Auto-link'
                                    )
                                )
                            );
                        }
                    )
                ),
                // Image generation modal
                showImageModal && createElement(
                    Modal,
                    {
                        title: i18n.generateImage || 'Afbeelding genereren',
                        onRequestClose: function() {
                            setShowImageModal(false);
                            setImagePrompt('');
                        }
                    },
                    createElement(
                        TextareaControl,
                        {
                            label: i18n.imagePrompt || 'Beschrijf de afbeelding',
                            value: imagePrompt,
                            onChange: setImagePrompt,
                            rows: 3
                        }
                    ),
                    createElement(
                        'div',
                        { className: 'writgoai-modal-actions' },
                        createElement(
                            Button,
                            {
                                variant: 'primary',
                                onClick: handleGenerateImage,
                                disabled: isLoading || !imagePrompt.trim()
                            },
                            isLoading ? createElement(Spinner, null) : null,
                            ' ',
                            i18n.generateBtn || 'Genereren'
                        ),
                        createElement(
                            Button,
                            {
                                variant: 'secondary',
                                onClick: function() {
                                    setShowImageModal(false);
                                    setImagePrompt('');
                                },
                                style: { marginLeft: '8px' }
                            },
                            i18n.cancel || 'Annuleren'
                        )
                    )
                )
            );
        };
    }, 'withWritgoAIToolbar');

    // Register the sidebar plugin
    registerPlugin('writgoai-sidebar', {
        render: WritgoAISidebar,
        icon: 'superhero'
    });

    // Add the block toolbar extension
    addFilter(
        'editor.BlockEdit',
        'writgocms/ai-toolbar',
        withWritgoAIToolbar
    );

})(window.wp);
