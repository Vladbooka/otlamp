<?php
//
// Capability definitions for the rss_client block.
//
// The capabilities are loaded into the database table when the block is
// installed or updated. Whenever the capability definitions are updated,
// the module version number should be bumped up.
//
// The system has four possible values for a capability:
// CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, and inherit (not set).
//
//
// CAPABILITY NAMING CONVENTION
//
// It is important that capability names are unique. The naming convention
// for capabilities that are specific to modules and blocks is as follows:
//   [mod/block]/<component_name>:<capabilityname>
//
// component_name should be the same as the directory name of the mod or block.
//
// Core moodle capabilities are defined thus:
//    moodle/<capabilityclass>:<capabilityname>
//
// Examples: mod/forum:viewpost
//           block/recent_activity:view
//           moodle/site:deleteuser
//
// The variable name for the capability definitions array follows the format
//   $<componenttype>_<component_name>_capabilities
//
// For the core capabilities, the variable is $moodle_capabilities.


$capabilities = array(
   // Право индивидуального размещения блока
   'block/notgraded:myaddinstance' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_SYSTEM,
       'archetypes' => array(
           'guest' => CAP_PREVENT,
           'user' => CAP_ALLOW,
           'manager' => CAP_ALLOW
       ),
        
       'clonepermissionsfrom' => 'moodle/my:manageblocks'
   ),
        
   // Право размещения блока
   'block/notgraded:addinstance' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
        
       'captype' => 'write',
       'contextlevel' => CONTEXT_BLOCK,
       'archetypes' => array(
           'guest' => CAP_PREVENT,
           'manager' => CAP_ALLOW
       ),
        
       'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
    
    // Whether or not a user can see the block.
    'block/notgraded:view' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'guest' => CAP_PREVENT,
            'student' => CAP_PREVENT,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
    
    // Whether or not a user can see the block with other's data
    'block/notgraded:view_others' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'guest' => CAP_PREVENT,
            'student' => CAP_PREVENT,
            'teacher' => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager' => CAP_ALLOW,
        ],
    ],
    
    'block/notgraded:viewall' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],

);

?>