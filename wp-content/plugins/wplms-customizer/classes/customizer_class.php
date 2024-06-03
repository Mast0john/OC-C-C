<?php

if(!class_exists('WPLMS_Customizer_Plugin_Class'))
{   
    class WPLMS_Customizer_Plugin_Class  // We'll use this just to avoid function name conflicts 
    {
            
        public function __construct()
	{   
            add_action('plugins_loaded',array($this,'wplms_define_constants'),5);  

        } // END public function __construct
        public function activate(){
        	// ADD Custom Code which you want to run when the plugin is activated
        }
        public function deactivate(){
        	// ADD Custom Code which you want to run when the plugin is de-activated	
        }
        
        // ADD custom Code in clas
	function wplms_define_constants(){

            if ( ! defined( 'WPLMS_COURSE_SLUG' ) )
                define( 'WPLMS_COURSE_SLUG', 'formation' );

            if ( ! defined( 'BP_COURSE_SLUG' ) )
                define( 'BP_COURSE_SLUG', 'formation' );

	    if ( ! defined( 'WPLMS_COURSE_CATEGORY_SLUG' ) )
                define( 'WPLMS_COURSE_CATEGORY_SLUG', 'categorie' );

            if ( ! defined( 'WPLMS_QUIZ_SLUG' ) )
                define( 'WPLMS_QUIZ_SLUG', 'questionnaire' );

            if ( ! defined( 'WPLMS_QUESTION_SLUG' ) )
                define( 'WPLMS_QUESTION_SLUG', 'question' );

            if ( ! defined( 'WPLMS_ASSIGNMENT_SLUG' ) )
                define( 'WPLMS_ASSIGNMENT_SLUG', 'note' );

            if ( ! defined( 'BP_COURSE_RESULTS_SLUG' ) )
                define( 'BP_COURSE_RESULTS_SLUG', 'resultat' );

            if ( ! defined( 'BP_COURSE_STATS_SLUG ' ) )
                define( 'BP_COURSE_STATS_SLUG', 'statistique' );

	    if ( ! defined( 'BP_GROUPS_SLUG ' ) )
                define( 'BP_GROUPS_SLUG', 'groupe' );
	    
	    if ( ! defined( 'BP_SETTINGS_SLUG ' ) )
                define( 'BP_SETTINGS_SLUG', 'reglage' );

	    if ( ! defined( 'BP_ACTIVITY_SLUG ' ) )
                define( 'BP_ACTIVITY_SLUG', 'activite' );

	    if ( ! defined( 'BP_FRIENDS_SLUG ' ) )
                define( 'BP_FRIENDS_SLUG', 'ami' );

	    if ( ! defined( 'BP_REGISTER_SLUG ' ) )
                define( 'BP_REGISTER_SLUG', 'inscription' );

	    if ( ! defined( 'BP_XPROFILE_SLUG ' ) )
                define( 'BP_XPROFILE_SLUG', 'profil' );

	    if ( ! defined( 'WPLMS_EVENT_SLUG' ) )
                define( 'WPLMS_EVENT_SLUG', 'evenement' );

	    if ( ! defined( 'WPLMS_NEWS_SLUG' ) )
                define( 'WPLMS_NEWS_SLUG', 'nouveautee' );

	    if ( ! defined( 'BP_NOTIFICATIONS_SLUG' ) )
                define( 'BP_NOTIFICATIONS_SLUG', 'notification' );

	    if ( ! defined( 'BP_SEARCH_SLUG' ) )
                define( 'BP_SEARCH_SLUG', 'recherche' );

	    if ( ! defined( 'BP_BLOGS_SLUG' ) )
                define( 'BP_BLOGS_SLUG', 'blog' );

	    if ( ! defined( 'BP_ACTIVATION_SLUG' ) )
                define( 'BP_ACTIVATION_SLUG', 'activation' );

	    if ( ! defined( 'BP_REGISTER_SLUG' ) )
                define( 'BP_REGISTER_SLUG', 'inscription' );

	    if ( ! defined( 'BP_FORUMS_SLUG' ) )
                define( 'BP_FORUMS_SLUG', 'forum' );
        }  
        
    } // END class WPLMS_Customizer_Class
} // END if(!class_exists('WPLMS_Customizer_Class'))

?>