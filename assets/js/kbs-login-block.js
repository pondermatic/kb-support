( function( blocks, editor, i18n ) {
    blocks.registerBlockType( 'kbs/login-block', {
        title: i18n.__( 'KBS Login', 'kb-support' ),
        icon: 'admin-users', // Use a WordPress dashicon or custom SVG
        category: 'common',

        edit: function() {
            return wp.element.createElement(
                'div',
                { className: 'kbs-login-block-editor' },
                i18n.__( 'KBS Login Form Placeholder', 'kb-support' )
            );
        },

        save: function() {
            // Rendering in PHP, so return null
            return null;
        },
    } );
} )( window.wp.blocks, window.wp.editor, window.wp.i18n );
