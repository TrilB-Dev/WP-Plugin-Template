(() => {
    'use strict';

    const insertMedia = (editor) => {
        if (!window.wp?.media) {
            return;
        }

        var frame = window.wp.media({
            title: window.pluginnameTinyMCE?.mediaTitle || '',
            button: { text: window.pluginnameTinyMCE?.mediaButton || '' },
            multiple: false
        });

        frame.on('select', () => {
            const attachment = frame.state().get('selection').first().toJSON();
            const url = attachment.url || '';
            if (url) {
                editor.insertContent('<img src="' + editor.dom.encode(url) + '" alt="' + editor.dom.encode(attachment.alt || attachment.title || '') + '">');
            }
        });

        frame.open();
    };

    const initialize = () => {
        if (!window.tinymce || typeof window.tinymce.init !== 'function') {
            return false;
        }

        document.querySelectorAll('.pluginname-tinymce-config').forEach((node) => {
            const editorId = node.dataset.editor;
            if (!editorId || window.tinymce.get(editorId)) {
                return;
            }

            let settings;
            try {
                settings = JSON.parse(node.textContent || '{}');
            } catch {
                return;
            }

            settings.setup = (editor) => {
                if (settings.media_buttons) {
                    editor.ui.registry.addButton('pluginnamemedia', {
                        icon: 'image',
                        tooltip: window.pluginnameTinyMCE?.mediaTooltip || '',
                        onAction: () => insertMedia(editor)
                    });
                }
            };

            window.tinymce.init(settings).catch((error) => {
                console.error('PluginName TinyMCE initialization failed.', error);
            });
        });

        return true;
    };

    const initializeWhenReady = () => {
        if (initialize()) {
            return;
        }

        window.setTimeout(initializeWhenReady, 50);
    };

    window.addEventListener('pluginname:settings-tab-loaded', initialize);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeWhenReady);
    } else {
        initializeWhenReady();
    }
})();