( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var ServerSideRender = wp.serverSideRender;
    var __ = wp.i18n.__;

    registerBlockType( 'kbs/login-block', {
        title: __( 'KBS Login', 'kb-support' ),
        icon: 'admin-users', // Use a WordPress dashicon or custom SVG
        category: 'common',

        edit: function( props ) {
            return wp.element.createElement(
                ServerSideRender,
                {
                    block: "kbs/login-block",
                    attributes: props.attributes
                }
            );
        },

        save: function() {
            // Rendering in PHP, so return null
            return null;
        },
    } );
} )( window.wp );

