<?php


class TavakalDestroySessionSettings
{
    /**
     * @var string $time_before_destroying_session_option_name
     * @var string $time_before_destroying_session_default_value
     *  for time option
     */
    public $time_before_destroying_session_option_name = 'tavakal_time_before_destroying_sessions';
    public $time_before_destroying_session_default_value = 4;

    /**
     * @var string $time_type_option_name
     * @var string $time_type_default_value
     * for time type option
     */
    public $time_type_option_name = 'tavakal_time_type';
    public $time_type_default_value = 'minutes';

    // for roles
    /**
     * @var string $included_roles_option_name
     * @var string $included_roles_default_value
     */
    public $included_roles_option_name = 'tavakal_included_roles';
    public $included_roles_default_value = ['subscriber' => 'on'];


    public function __construct()
    {
        add_action('admin_init', [$this, 'generate_options']);
        add_action('update_option_' . $this->time_before_destroying_session_option_name, [$this, 'save_time_before_destroying_sessions'], 10, 2);
        add_action('update_option_' . $this->included_roles_option_name, [$this, 'save_included_roles'], 10, 2);
        add_action('update_option_' . $this->time_type_option_name, [$this, 'save_time_type'], 10, 2);
    }

    /**
     * @param $old_value
     * @param $value
     * @return void
     */
    public function save_time_before_destroying_sessions($old_value, $value)
    {
        if ($old_value === $value || (int)$value <= 0) {
            return;
        }
        update_option($this->time_before_destroying_session_option_name, $value);
    }

    /**
     * @param $old_value
     * @param $value
     * @return void
     */
    public function save_included_roles($old_value, $value)
    {
        update_option($this->included_roles_option_name, $value);
    }

    public function save_time_type($old_value, $value)
    {
        // IF NOT HOURS OR MINUTES, ABORT
        $enum_values = ['minutes', 'hours'];
        if ($value === $old_value || !in_array($value, $enum_values)) {
            return;
        }

        update_option($this->time_type_option_name, $value);
    }

    /**
     * @return void
     */
    public function generate_options()
    {
        // register time option
        register_setting('general', $this->time_before_destroying_session_option_name);
        // register time type option
        register_setting('general', $this->time_type_option_name);
        // register roles option
        register_setting('general', $this->included_roles_option_name);

        add_settings_section(
            'destroy_user_sessions',
            'Tavakal - Destroy user sessions',
            '',
            'general'
        );

        add_settings_field(
            'time_before_session_destroy_input',
            '<label for="tavakal_time_before_destroying_sessions">' . __('Time before session destroy') . '</label>',
            array($this, 'time_before_session_destroy_input'),
            'general',
            'destroy_user_sessions'
        );

        add_settings_field(
            'tavakal_time_type',
            '<label for="tavakal_time_type">' . __('Time Type') . '</label>',
            array($this, 'time_type_input'),
            'general',
            'destroy_user_sessions'
        );

        add_settings_field(
            'tavakal_included_roles',
            '<label for="roles_included">' . __('Roles included') . '</label>',
            array($this, 'roles_included'),
            'general',
            'destroy_user_sessions'
        );

    }

    /**
     * @return void
     */
    public function time_before_session_destroy_input()
    {
        $hours_after_session_destroy = get_option($this->time_before_destroying_session_option_name);
        if (!$hours_after_session_destroy) {
            $hours_after_session_destroy = $this->time_before_destroying_session_default_value;
            add_option($this->time_before_destroying_session_option_name, $hours_after_session_destroy);
        }
        echo '<input type="number" value="' . esc_html($hours_after_session_destroy) . '" placeholder="'.esc_html('For example: 5').'" name="' . esc_html($this->time_before_destroying_session_option_name) . '" >';
    }

    /**
     * @return void
     */
    public function time_type_input()
    {
        $time_type = get_option($this->time_type_option_name);
        if (!$time_type) {
            $time_type = $this->time_type_default_value;
            add_option($this->time_type_option_name, $time_type);
        }
        echo '
        <select name="' . esc_html($this->time_type_option_name) . '">
        <option value="' . esc_html($time_type) . '">' . esc_html(ucfirst($time_type)) . '</option>
        <option value="' . esc_html('hours') . '">' . esc_html('Hours') . '</option>
        <option value="' . esc_html('minutes') . '">' . esc_html('Minutes') . '</option>
        </select>
        ';
    }

    public function roles_included()
    {
        $roles = wp_roles();
        $included_roles = get_option('tavakal_included_roles');
        if (!$included_roles) {
            $included_roles = $this->included_roles_default_value;
            add_option('tavakal_included_roles', $included_roles);
        }

        foreach ($roles->roles as $role_name => $data) {
            $checked = isset($included_roles[$role_name]) && $included_roles[$role_name] === 'on' ? 'checked' : '';
            echo '<input type="checkbox" name="tavakal_included_roles[' . esc_html($role_name) . ']" ' . esc_html($checked) . '>' . esc_html($role_name) . "<br>";
        }
    }

}