<?php
namespace block_mastercourse\eduportal;

class nmfo extends \block_mastercourse\eduportal {
    
    static protected $serviceshortname = 'nmfo';
    static public $enabled = false;
    
    protected $statuscodes = [
        'not_published',
        'sent_for_publication',
        'on_review',
        'published',
        'sent_to_unpublish',
        'rejected',
        'error'
    ];
    
    static protected $in_progress = [
        'sent_for_publication',
        'on_review',
        'sent_to_unpublish',
    ];
    
    /**
     * Доступные статусы в зависимости от текущего
     *
     * @param string $coursepubstatus - код текущего статуса
     * @param boolean $manual
     * @return array массив допустимых к смене статусов (код => имя)
     */
    public function get_available_status_codes($coursepubstatus, $manual=true)
    {
        $available = [];
        
        switch($coursepubstatus)
        {
            case 'not_published':
                $available[] = 'sent_for_publication';
                break;
            case 'sent_for_publication':
                $available[] = 'sent_to_unpublish';
                break;
            case 'on_review':
                $available[] = 'sent_to_unpublish';
                break;
            case 'published':
                $available[] = 'sent_to_unpublish';
                break;
            case 'rejected':
                $available[] = 'sent_for_publication';
                $available[] = 'sent_to_unpublish';
                break;
            case 'error':
                $available[] = 'sent_for_publication';
                $available[] = 'sent_to_unpublish';
                break;
            case 'sent_to_unpublish':
                $available[] = 'sent_for_publication';
                break;
            default:
                // возможность инициализировать статус в начальное положение
                $available[] = 'not_published';
                break;
        }
        
        
        if (!$manual)
        {
            foreach(['on_review', 'published', 'rejected', 'not_published', 'error'] as $autostatus)
            {
                if ($autostatus != $coursepubstatus && !in_array($autostatus, $available))
                {
                    $available[] = $autostatus;
                }
            }
        }
        
        return $available;
    }
}