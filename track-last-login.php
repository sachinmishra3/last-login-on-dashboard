<?php
/**
Plugin Name: Last login on dashboard 
Plugin URI: https://www.linkedin.com/in/mishrasachin
Description: Check your last login on admin dashboard widget. Shows the Last IP address and time when you last time logged in. Plugin for security purpose. No effect on frontend.
Author: Sachin Mishra
Author URI: https://www.linkedin.com/in/mishrasachin
Version: 1.1
**/

// Start keeping user's information
function save_admin_login_details( $user_login, $user){
	global $wp;

	$user_data['user_ip'] 		= 	get_current_ip(); // get user ip 
	$user_data['login_time']	=	time(); // make time in current format
	$user_data['browser']		=	get_browser_name();
	
	$user_data_ser = maybe_serialize($user_data);

    update_user_meta($user->ID, 'sm_last_login', $user_data_ser); // save the secret 
}
// save upon login
add_action( 'wp_login', 'save_admin_login_details', 10, 2 );


/*
*
* Try to find the IP address /
*/
function get_current_ip(){
	// Check all possible headers for IP address
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		// Handle multiple IPs in case of proxies
		$ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$ip = trim(end($ipList)); // Get the last IP in the chain
	} elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
		$ip = $_SERVER['HTTP_X_REAL_IP']; // Some setups use this header
	} else {
		$ip = $_SERVER['REMOTE_ADDR']; // Default IP
	}
	return $ip;
}

function get_browser_name() {
    // Check for HTTP_SEC_CH_UA first
    /*if (!empty($_SERVER['HTTP_SEC_CH_UA'])) {
        $secChUa = $_SERVER['HTTP_SEC_CH_UA'];
        // Match the browser name from HTTP_SEC_CH_UA
        if (preg_match('/"([^"]+)"/', $secChUa, $matches)) {
	            $browser = stripslashes($matches[1]); // Return the first matched name
				
        }
    }*/

    // Fallback to HTTP_USER_AGENT
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    // Define patterns for common browsers
    $browsers = [
        'Edge' => 'Edg',
        'Opera' => 'OPR|Opera',
        'Firefox' => 'Firefox',
        'Chrome' => 'Chrome',
        'Safari' => 'Safari',
        'IE' => 'MSIE|Trident',
    ];

    foreach ($browsers as $name => $pattern) {
        if (preg_match("/($pattern)/i", $userAgent)) {
            return $name;
        }
    }

    return 'Unknown Browser';
}
// Lay the foundation of your widget.
function create_last_login_dashboard_widget() {
    wp_add_dashboard_widget(
        widget_id: 'last_login_dashboard_widget', // Widget slug
        widget_name: 'Last Login Details', // Widget title
        callback: 'display_last_login_widget' // Function to display the widget
    );
}
add_action( 'wp_dashboard_setup', 'create_last_login_dashboard_widget' );

// Show time!! Showcase the information you have
function display_last_login_widget() {
    // show it on the widget
	$user_id = get_current_user_id();
	if ( metadata_exists( 'user', $user_id, 'sm_last_login' ) ) {
		$user_meta_value = get_user_meta($user_id,'sm_last_login', true);
		$user_data = maybe_unserialize($user_meta_value);
		$ip = $user_data['user_ip'];
		$browser = $user_data['browser'];
		$login_time = human_time_diff($user_data['login_time']);
		echo "<p class='user-login-detail'><b>Last Login: </b>".$login_time."</p>";
		echo "<p class='user-login-detail'><b>IP Address: </b>".$ip."</p>";
		echo "<p class='user-login-detail'><b>Browser </b>".$browser."</p>";
	}
}

