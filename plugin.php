<?php
    
    if (!class_exists('IMAGETS_Plugin')) {

        class IMAGETS_Plugin{

            public static $plugin_file = '';
            public $plugin_name;
            public $plugin_basename;
            public $plugin_path;
            public $plugin_url;
            private $options;

            /**
             * Construct the plugin object
             */
            public function __construct(){
                // Initialize Settings
                require_once(sprintf("%s/views/settings.php", dirname(__FILE__)));


                $IMAGETS_Settings = new IMAGETS_Settings($this);

                // Get options
                $this->options = get_option('IMAGETS_settings');

                // Register actions
                add_action('admin_enqueue_scripts', array($this, 'register_admin_styles'));
                add_action('wp_ajax_imagets_response', array($this, 'imagets_ajax_process'));
                add_action('wp_ajax_imagets_fetch', array($this, 'imagets_ajax_fetch'));
                add_action('admin_footer', array($this, 'imagets_admin_footer'));
                add_action('template_redirect', array($this, 'imagets_manager'));

                // Register Settings
                self::$plugin_file = IMAGETS_PLUGIN_MAIN_FILE_PATH;
                $this->plugin_name = strtolower(plugin_basename(dirname(self::$plugin_file)));
                $this->plugin_basename = plugin_basename(self::$plugin_file);
                $this->plugin_path = plugin_dir_path(self::$plugin_file);
                $this->plugin_url  = plugin_dir_url(self::$plugin_file);
                $this->licence     = get_option('imagets_licence') ? get_option('imagets_licence') : @$licence;
                $this->userAgent   = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36';
            }

            /**
             * Manager Screen
             */
            public function imagets_admin_footer(){
                global $post;

                $option_count = get_option('count');
                $option_imgsz = get_option('imgsz');
                $option_start_index = get_option('start_index');
                $option_editor_mode = get_option('editor_mode');
                $imagets_language = get_option('imagets_language');
                $post_id = $post->ID;
                $licence = $this->licence;

                require_once(sprintf("%s/views/manager.php", dirname(__FILE__)));
            }

            /**
             * Redirects
             */
            public function imagets_manager(){
                //Proxy Image
                if(intval(@$_GET['imagets']) == 3) {
                    $url = urldecode(@$_GET['url']);

                    $response = wp_remote_get( $url , array( 'timeout' => 120, 'user-agent' => $this->userAgent , 'headers' => array('Referer' => $url)));

                    header('Content-Type: '.$response['headers']['content-type']);
                    echo $response['body'];
                    exit;
                }
            }

            /**
             * Register and enqueue admin styles and localize
             */
            public function register_admin_styles(){
                global $post; 

                wp_register_style('IMAGETS-admin-styles', plugins_url('assets/css/admin.css', __FILE__));
                wp_enqueue_style('IMAGETS-admin-styles');

                wp_register_style('IMAGETS-manager-styles', plugins_url('assets/css/manager.css', __FILE__));
                wp_enqueue_style('IMAGETS-manager-styles');

                wp_register_style('IMAGETS-bootstrap-styles', plugins_url('assets/css/bootstrap.min.css', __FILE__));
                wp_enqueue_style('IMAGETS-bootstrap-styles');

                wp_register_script('IMAGETS-admin-settings', plugins_url('assets/js/settings.js', __FILE__));
                wp_enqueue_script('IMAGETS-admin-settings');

                wp_register_script('IMAGETS-admin-post', plugins_url('assets/js/post.js', __FILE__));
                wp_enqueue_script('IMAGETS-admin-post');

                wp_register_script('IMAGETS-engine-post', plugins_url('assets/js/engine.min.js', __FILE__));
                wp_enqueue_script('IMAGETS-engine-post');

                wp_register_script('IMAGETS-main-post', plugins_url('assets/js/main.js', __FILE__));
                wp_enqueue_script('IMAGETS-main-post');

                wp_localize_script('IMAGETS-admin-post', 'imagets_ajax_script', array( 
                    'ajaxurl' => admin_url( 'admin-ajax.php' ), 
                    'post_id' => $post->ID,
                    'plugin_url' => $this->plugin_url,
                    'site_url' => site_url()
                    ));
            }

            /**
             * ImageTS Make
             */

            public function imagets_make($images = array()){
                $quality       = get_option('quality');
                $flip          = get_option('flip');

                $layer         = get_option('layer');
                $crop_x        = get_option('crop_x');
                $crop_y        = get_option('crop_y');
                $crop          = array($crop_x, $crop_y);

                $border_visible    = get_option('border_visible');
                $watermark_visible = get_option('watermark_visible');

                if($watermark_visible){
                    $watermark     = get_option('watermark');
                    $opacity       = get_option('opacity');
                    $w_percent     = get_option('w_percent');
                    $position      = get_option('position');
                }

                if($border_visible){
                    $border_weight  = get_option('border_weight');
                    $border_color   = get_option('border_color');
                    $border         = array('color'=> $border_color, 'weight'=> $border_weight);
                }
                
                $width         = get_option('width');
                $height        = get_option('height');

                $apply  = array(
                    'quality'   => $quality,
                    'flip'      => $flip,
                    'watermark' => @$watermark,
                    'opacity'   => @$opacity,
                    'w_percent' => @$w_percent,
                    'position'  => @$position,
                    'quality'   => $quality,
                    'layer'     => $layer,
                    'crop'      => $crop,
                    'border'    => @$border,
                    'width'     => $width,
                    'height'    => $height
                );
                $array  = array('images' => $images, 'apply' => $apply);

                $response = wp_remote_post('http://imagets.com/api/'. $this->licence, array(
                    'method' => 'POST',
                    'body' => json_encode($array)
                ));

                if (!is_wp_error($response)) {
                    return json_decode($response['body']);
                }else{
                    return array();
                }
            }

            /**
             * Wordpress attach image
             */

            public function attach_image_url($file, $post_id, $data) {
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                require_once(ABSPATH . "wp-admin" . '/includes/media.php');
                if ( ! empty($file) ) {
                    // Download file to temp location
                    $tmp = download_url( $file );
                    // Set variables for storage
                    // fix file filename for query strings
                    preg_match('/[^\?]+\/(.*?)\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $file, $matches);
                    $file_array['name'] = sanitize_title($data['post_name']).'.'.$matches[2];
                    $file_array['tmp_name'] = $tmp;
                    // If error storing temporarily, unlink
                    if ( is_wp_error( $tmp ) ) {
                        @unlink($file_array['tmp_name']);
                        $file_array['tmp_name'] = '';
                    }
                    // do the validation and storage stuff
                    $id = media_handle_sideload( $file_array, $post_id, $data['post_name'], $data);
                    // If error storing permanently, unlink
                    if ( is_wp_error($id) ) {@unlink($file_array['tmp_name']);}
                    add_post_meta($post_id, '_thumbnail_id', $id, true);
                    //wp_update_attachment_metadata($id, $data);

                    return $id;
                }else{
                    return null;
                }
            }

            /**
             * Catch ajax process from manager
             */

            public function imagets_ajax_process() {
                $url     = sanitize_text_field($_POST['image_url']);
                $url     = strtok($url, '?');
                $desc    = @$_POST['image_desc'] ? sanitize_text_field($_POST['image_desc']) : ' ';
                $url     = sanitize_text_field(@$_POST['image_url']);
                $title   = sanitize_text_field(@$_POST['image_title']);
                $tags    = sanitize_text_field(@$_POST['image_tags']);

                $data = array(
                    'post_name' => $title,
                    'post_content' => $desc,
                    'post_excerpt' => $desc,
                    'post_tag' => $tags,
                    );

                $post_id = (int) $_POST['post_id'];

                if( @$_POST['append_type'] == 'not_download' ) {
                    echo '<img src="'.$url.'" alt="'.$data['post_name'].'" title="'.$data['post_name'].'">';
                    exit;
                }

                $result  = $this->imagets_make(array($url));

                foreach ($result as $key => $value) {
                    if ( @$_POST['append_type'] == 'append' ) {

                        $id = is_wp_error($image = $this->attach_image_url($value->src, $post_id, $data)) ? '' : $image;

                        if(get_option('auto_tag') == 'true' && $id){
                            echo '[caption id="'.$id.'" align="alignnone"]';
                            echo wp_get_attachment_image( $id, 'full' );
                            echo ''.$data['post_name'].'[/caption]';
                        }else{
                            echo wp_get_attachment_image( $id, 'full' );
                        }

                    }elseif ( @$_POST['append_type'] == 'gallery' || @$_POST['append_type'] == 'only_download' ) {

                        $id = is_wp_error($image = $this->attach_image_url($value->src, $post_id, $data)) ? '' : $image;
                        echo $id;

                    }

                    if($_POST['is_featured']){
                        set_post_thumbnail( $post_id, $id );
                    }
                }

                exit;
            }

            public function strposa($haystack, $needles=array(), $offset=0) {
                $chr = array();

                foreach($needles as $needle) {
                    $res = strpos($haystack, $needle, $offset);

                    if ($res !== false)
                        $chr[$needle] = $res;
                }

                if (empty($chr))
                    return false;

                return min($chr);
            }

            public function black_list($value){
                $blackList = array('640x640', '480x480', '136x', '736x', 'avatars', '236x', 'refresh.gif', 'thumbnail');

                return $this->strposa($value, $blackList);
            }

            public function proxy($value){
                return get_option('thumb_proxy') == 'true' ? site_url() . '?imagets=3&url=' . urlencode($value) : $value;
            }

            /**
             * Catch ajax process for fetching
             */

            public function imagets_ajax_fetch() {
                $url     = $_POST['url'];
                $url     = strtok($url, '?');
                $output  = array();

                $response = wp_remote_get( $url , array( 'timeout' => 120, 'user-agent' => $this->userAgent, 'sslverify' => false ));
                if( is_array($response) ) {
                    $header = $response['headers'];
                    $body = $response['body'];

                    //preg_match("/<body.*\/body>/s", $body, $matches);
                    //$body = $matches[0];

                    $images = array();

                    preg_match_all('/<img(.*?)src=("|\'|)(.*?)("|\'| )(.*?)>/s', $body, $tags);
                    preg_match_all('/([a-z]+[:.].*?(jpg|png|gif|jpeg))/i', $body, $encoded);


                    //Image tags
                    foreach ($tags[3] as $key => $value) {
                        $value = str_replace(array('-150x150'), array(''), $value);
                        
                        $images[] = $value;
                    }

                    //Pinterest, instagram, json encoded sites
                    foreach ($encoded[1] as $key => $value) {
                        $value = str_replace(array('\/'), array('/'), $value);

                        if (!$this->black_list($value)) {
                            $images[] = $value;
                        }
                    }

                    $images = array_unique($images);
                    foreach ($images as $key => $value) {
                        if($value){
                            $output[] = array(
                                'tbUrl' => $this->proxy($value),
                                'url' => $value,
                                'title' => null,
                                );
                        }
                    }
                }

                echo json_encode($output);

                exit;
            }

            /**
             * Activate the plugin
             */
            public static function activate(){

            }

            /**
             * Deactivate the plugin
             */
            public static function deactivate()
            {
                
            }

            /**
             * Uninstall the plugin
             */
            public static function uninstall(){
                delete_option('IMAGETS_settings');
            }

        }

    }

    function IMAGETS_Plugin_init(){
        if (class_exists('IMAGETS_Plugin')) {

            $IMAGETS = new IMAGETS_Plugin();

            // Add a link to the settings page onto the plugin page
            if (isset($IMAGETS)) {

                // Add the settings link to the plugins page
                function IMAGETS_plugin_settings_link($links){
                    $settings_link = '<a href="options-general.php?page=' . 'IMAGETS' . '">ImageTS Settings</a>';
                    array_unshift($links, $settings_link);
                    return $links;
                }

                add_filter("plugin_action_links_" . plugin_basename(IMAGETS_PLUGIN_MAIN_FILE_PATH), 'IMAGETS_plugin_settings_link');
            }
        }
    }
?>