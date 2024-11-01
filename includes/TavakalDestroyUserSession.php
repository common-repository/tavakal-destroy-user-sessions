<?php

class TavakalDestroyUserSession
{
    /**
     * @var string $last_activity_meta_key
     */
    public  $last_activity_meta_key = 'tavakal_last_user_activity';

    public function __construct()
    {

        // add five min cron
        add_filter('cron_schedules', [$this, 'cron_add_five_min']);
        // update user
        add_action('init', [$this, 'update_last_activity']);
        // destroy expired sessions
        add_action('tavakal_destroy_expired_sessions', [$this, 'destroy_expired_sessions']);
        // adding schedule
        if (!wp_next_scheduled('tavakal_destroy_expired_sessions')) {
            wp_schedule_event(time(), 'five_min', 'tavakal_destroy_expired_sessions');
        }
    }

    /**
     * @param $schedules
     * @return mixed
     */
    public function cron_add_five_min($schedules)
    {
        $schedules['five_min'] = [
            'interval' => 60 * 5,
            'display' => 'Each 5 min'
        ];
        return $schedules;
    }

    /**
     * @return void
     */
    public function update_last_activity()
    {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return;
        }
        if ($this->user_is_included($user_id)) {
            update_user_meta($user_id, $this->last_activity_meta_key, time());
        }
    }

    /**
     * @return void
     */
    public function destroy_expired_sessions()
    {
        global $wpdb;
        $time_type = get_option('tavakal_time_type');
        $hours_after_session_destroy = get_option('tavakal_time_before_destroying_sessions');
        $time_earlier = strtotime("-{$hours_after_session_destroy} $time_type");
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->usermeta} WHERE {$wpdb->usermeta}.meta_key = '{$this->last_activity_meta_key}' AND meta_value < {$time_earlier}", OBJECT);

        foreach ($results as $result) {
            if (!$this->user_is_included($result->user_id)) {
                continue;
            }
            $this->destroy_user_sessions($result->user_id);
            delete_user_meta($result->user_id, $this->last_activity_meta_key);
        }
    }

    /**
     * @param $user_id
     * @return void
     * destroy user sessions
     */
    public function destroy_user_sessions($user_id)
    {
        // get all sessions for user with ID $user_id
        $sessions = WP_Session_Tokens::get_instance($user_id);
        // we have got the sessions, destroy them all!
        $sessions->destroy_all();
    }

    /**
     * @param $user_id
     * @return bool
     */
    private function user_is_included($user_id)
    {
        $user_meta = get_userdata($user_id);
        $user_roles = $user_meta->roles;
        $included_roles = get_option('tavakal_included_roles');
        $included_roles = is_array($included_roles) ? array_keys($included_roles) : [];
        foreach ($user_roles as $role) {
            return in_array($role, $included_roles);
        }
        return false;
    }

}