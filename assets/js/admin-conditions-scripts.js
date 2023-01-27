
jQuery(document).on('change', '.kbs_option_disable_tickets input', function() { 
   
    if(this.checked) {


            jQuery( '.kbs_option_enable_sequential, .kbs_option_sequential_start, .kbs_option_ticket_prefix, .kbs_option_ticket_suffix, .kbs_option_show_count, .kbs_option_show_count_menubar, .kbs_option_enable_participants, .kbs_option_hide_closed, .kbs-settings-sub-nav li' ).css('display', 'none' );
          

    }else{

            jQuery( '.kbs_option_enable_sequential, .kbs_option_sequential_start, .kbs_option_ticket_prefix, .kbs_option_ticket_suffix, .kbs_option_show_count, .kbs_option_show_count_menubar, .kbs_option_enable_participants, .kbs_option_hide_closed, .kbs-settings-sub-nav li' ).css('display', '' );
          
    }
});

jQuery(document).ready(function ($) {

    if( jQuery('.kbs_option_disable_tickets input').is(':checked') && $( '.kbs_option_disable_tickets input' ).length ) {


            jQuery( '.kbs_option_enable_sequential, .kbs_option_sequential_start, .kbs_option_ticket_prefix, .kbs_option_ticket_suffix, .kbs_option_show_count, .kbs_option_show_count_menubar, .kbs_option_enable_participants, .kbs_option_hide_closed, .kbs-settings-sub-nav li' ).css('display', 'none' );
           // jQuery( '.kbs-settings-sub-nav li' ).css('display', 'none' );
            
    }else{

            jQuery( '.kbs_option_enable_sequential, .kbs_option_sequential_start, .kbs_option_ticket_prefix, .kbs_option_ticket_suffix, .kbs_option_show_count, .kbs_option_show_count_menubar, .kbs_option_enable_participants, .kbs_option_hide_closed, .kbs-settings-sub-nav li' ).css('display', '' );
          //  jQuery( '.kbs-settings-sub-nav li' ).css('display', '' );
    }
});



jQuery(document).on('change', '.kbs_option_disable_kb_articles input', function() { 
   
    if(this.checked) {

            jQuery( '.kbs_option_article_restricted, .kbs_option_restricted_login, .kbs_option_article_hide_restricted, .kbs_option_article_hide_restricted_ajax, .kbs_option_article_num_posts_ajax, .kbs_option_article_excerpt_length, .kbs_option_count_agent_article_views, .kbs_option_article_views_dashboard, .kbs-settings-sub-nav' ).css('display', 'none' );

    }else{

        jQuery( '.kbs_option_article_restricted, .kbs_option_restricted_login, .kbs_option_article_hide_restricted, .kbs_option_article_hide_restricted_ajax, .kbs_option_article_num_posts_ajax, .kbs_option_article_excerpt_length, .kbs_option_count_agent_article_views, .kbs_option_article_views_dashboard, .kbs-settings-sub-nav' ).css('display', '' );
          
    }
});

jQuery(document).ready(function ($) {

    if( jQuery('.kbs_option_disable_kb_articles input').is(':checked') && $( '.kbs_option_disable_kb_articles input' ).length ) {

        jQuery( '.kbs_option_article_restricted, .kbs_option_restricted_login, .kbs_option_article_hide_restricted, .kbs_option_article_hide_restricted_ajax, .kbs_option_article_num_posts_ajax, .kbs_option_article_excerpt_length, .kbs_option_count_agent_article_views, .kbs_option_article_views_dashboard, .kbs-settings-sub-nav' ).css('display', 'none' );

    }else{

        jQuery( '.kbs_option_article_restricted, .kbs_option_restricted_login, .kbs_option_article_hide_restricted, .kbs_option_article_hide_restricted_ajax, .kbs_option_article_num_posts_ajax, .kbs_option_article_excerpt_length, .kbs_option_count_agent_article_views, .kbs_option_article_views_dashboard, .kbs-settings-sub-nav' ).css('display', '' );
          
    }
});