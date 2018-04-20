<?php

namespace block_quickmail\forms\concerns;

trait is_quickmail_form {

    /**
     * Returns a constructed query string including the given parameters
     * 
     * @param  array  $params
     * @return string
     */
    public static function generate_target_url($params = [])
    {
        $target = '?' . http_build_query($params, '', '&');

        return $target;
    }

    /////////////////////////////////////////
    ///
    /// Error Handling / Rendering
    /// 
    /////////////////////////////////////////

    /**
     * Sets errors on this form's error stack received from the given exception
     * 
     * @param array $errors
     * @return void
     */
    public function set_error_exception($exception)
    {
        // if no errors set yet, create stack container
        $this->errors = ! is_null($this->errors) ? $this->errors : [];

        // handle persistent exceptions
        if (get_class($exception) == 'core\invalid_persistent_exception') {
            $this->errors[] = $exception->a;
        } else if (get_class($exception) == 'block_quickmail\exceptions\validation_exception') {
            // if the "errors" is not an array, make it an array
            if ( ! is_array($exception->errors)) {
                $this->errors = array_merge($this->errors, [$exception->errors]);
            } else {
                $this->errors = array_merge($this->errors, $exception->errors);
            }
        }
    }

    /**
     * Renders a moodle error notification if there are any errors
     * 
     * @return string
     */
    public function render_error_notification()
    {
        if (count($this->errors)) {
            $html = '<ul style="margin-bottom: 0px;">';
            
            foreach ($this->errors as $error) {
                $html .= '<li>' . $error . '</li>';
            }

            $html .= '</ul>';
            
            \core\notification::error($html);
        }
    }

}