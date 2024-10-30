<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('IMAGETS_Settings')) {
    class IMAGETS_Settings
    {
        protected $parent = null;
        protected $plugin_name;
        protected $plugin_basename;
        protected $plugin_path;
        protected $plugin_url;
        private $options;

        /**
         * Construct the plugin object
         */
        public function __construct($parent)
        {
            $this->parent = &$parent;
            $this->plugin_name = $parent->plugin_name;
            $this->plugin_basename = $parent->plugin_basename;
            $this->plugin_path = $parent->plugin_path;
            $this->plugin_url = $parent->plugin_url;
            $this->options = get_option('IMAGETS_settings');
            $this->licence = get_option('imagets_licence') ? get_option('imagets_licence') : @$licence;

            $this->actions();
        }

        function actions () {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'settings_init'));
        }

        function add_admin_menu() {

            add_options_page(
                IMAGETS_CONFIG_MENU_TEXT,
                IMAGETS_CONFIG_MENU_TEXT,
                'manage_options',
                'IMAGETS',
                array($this, 'options_page')
            );

        }

        /*
         * Setting Sections & Fields
         */
        function settings_init(){

            register_setting(
                'IMAGETS_settings', // Settings page
                'IMAGETS_settings', // Option name
                array($this, 'settings_callback') // callback
            );

            register_setting('imagets_settings', 'quality' );
            register_setting('imagets_settings', 'flip' );
            register_setting('imagets_settings', 'watermark_visible' );
            register_setting('imagets_settings', 'watermark' );
            register_setting('imagets_settings', 'position' );
            register_setting('imagets_settings', 'opacity' );
            register_setting('imagets_settings', 'w_percent' );
            register_setting('imagets_settings', 'layer' );
            register_setting('imagets_settings', 'crop_x' );
            register_setting('imagets_settings', 'crop_y' );
            register_setting('imagets_settings', 'border_weight' );
            register_setting('imagets_settings', 'border_visible' );
            register_setting('imagets_settings', 'border_color' );
            register_setting('imagets_settings', 'count' );
            register_setting('imagets_settings', 'width' );
            register_setting('imagets_settings', 'height' );
            register_setting('imagets_settings', 'imgsz' );
            register_setting('imagets_settings', 'start_index' );
            register_setting('imagets_settings', 'auto_mode' );
            register_setting('imagets_settings', 'auto_cron' );
            register_setting('imagets_settings', 'auto_share' );
            register_setting('imagets_settings', 'auto_time' );
            register_setting('imagets_settings', 'auto_tag' );
            register_setting('imagets_settings', 'editor_mode' );
            register_setting('imagets_settings', 'thumb_proxy' );
            register_setting('imagets_settings', 'imagets_suggestion' );
            register_setting('imagets_settings', 'imagets_language' );
            register_setting('imagets_settings', 'imagets_desc' );
            register_setting('imagets_settings', 'imagets_licence' );

            add_option('quality', '75');
            add_option('count', '8');
            add_option('imgsz', 'medium');
            add_option('start_index', '0');
            add_option('auto_mode', '0');
            add_option('auto_time', 'daily');
            add_option('editor_mode', 'normal');
            add_option('imagets_licence', $this->licence);
        }

        function settings_callback( $input ) {
            return $input;
        }

        function options_page(){

            echo '<form method="post" id="imagets_settings" action="options.php" style="width:800px">';
            echo '<img id="preview-img">';
            ?>

            <div id="infobox" class="postbox-container">
                <div class="meta-box-sortables">
                    <div class="postbox">
                        <h3><span>Resources & Support</span></h3>
                        <div class="inside">
                            <ul>
                                <li><a href="http://imagets.com/docs" target="_blank">Documantion</a></li>
                                <li><a href="mailto:support@imagets.com" target="_blank">Mail Support</a></li>
                                <li><a href="skype:imagets" target="_blank">Skype Support</a></li>
                            </ul>
                            <p>Copyright 2015 <a target="_blank" href="http://imagets.com">imagets.com</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php

            settings_fields( 'imagets_settings' );
            do_settings_sections( 'imagets_settings' );

            echo '<table class="wp-list-table widefat fixed plugins" style="padding-left:15px;margin-top:20px;padding-bottom:20px">';

                $filters     = array('Yok' => '','White' => 'white', 'Canvas' => 'canvas', 'Bricks' => 'bricks', 'Dots' => 'dots', 'Blue-Light' => 'insta-blue', 'Red' => 'red', 'Bulutlar' => 'clouds', 'Yeşil' => 'green', 'Vignette' => 'vignette', 'Album' => 'album', 'Old Photo' => 'oldphoto');
                $image_sizes = array('Çok büyük' => 'huge','Büyük' => 'large', 'Orta' => 'medium', 'Küçük' => 'small');
                $positions   = array('Yukarıda solda' => 'upperLeft', 'Aşağıda solda' => 'lowerLeft', 'Yukarıda sağda' => 'upperRight', 'Aşağıda sağda' => 'lowerRight', 'Tamamen ortalı' => 'center');
                $image_formats = array('Galeri formunda' => '1','Yazı altına ekle' => '0');
                $time_formats  = array('Saatlik' => 'hourly', 'Günlük' => 'daily');
                $modes  = array('Normal' => 'normal', 'Profesyonel' => 'pro');
                $langs  = array('Türkçe' => 'tr', 'English' => 'en', 'Deutsch' => 'de', 'Français' => 'fr', 'Español' => 'es', 'русский' => 'ru', 'Global' => '');

                $form_licence  = array(
                    'Lisans Kodu' => array('name' => 'imagets_licence', 'desc' => 'Hesabınıza giriş yaptığınızda edindiğiniz lisans kodu.')
                    );

                $form_images  = array(
                    'Kalite' => array('name' => 'quality', 'desc' => '0-100 arasında olmalı.', 'type' => 'range'),
                    'Filtre' => array('name' => 'layer', 'desc' => 'Resmin üzerine üzerine filtre ekleyin.', 'options' => $filters),
                    'Ters çevir' => array('name' => 'flip', 'desc' => 'Resmi yatay olarak ters çevirir.', 'type' => 'checkbox')
                    );

                $form_watermark  = array(
                    'Watermark (Logo)' => array('name' => 'watermark_visible', 'desc' => 'Logo durumunu seçin.', 'type' => 'checkbox'),
                    'Logo adresi' => array('name' => 'watermark', 'desc' => 'Başında http olmalı, url formatında olmalı, PNG olmasını tercih edin.'),
                    'Posizyonu' => array('name' => 'position', 'desc' => 'Logonun resim üzerindeki posizyonu.', 'options' => $positions),
                    'Şeffaflık' => array('name' => 'opacity', 'desc' => '0-100 arasında olmalı.', 'type' => 'number'),
                    'Oran' => array('name' => 'w_percent', 'desc' => '0-100 arasında olmalı.', 'type' => 'number'),
                    );

                $form_border  = array(
                    'Çerçeve' => array('name' => 'border_visible', 'desc' => 'Çerçeve durumunu seçin.', 'type' => 'checkbox'),
                    'Çerçeve rengi' => array('name' => 'border_color', 'desc' => 'Renk seçin', 'type' => 'color'),
                    'Çerçeve kalınlığı' => array('name' => 'border_weight', 'desc' => 'Sayı değeri olmalı, bu değer pixel cinsindendir.', 'type' => 'range'),
                    );

                $form_scale  = array(
                    'Yatay kırp' => array('name' => 'crop_x', 'desc' => 'Yatay olarak resmi kırpar, yüzde cinsindendir. (0-100)', 'type' => 'number'),
                    'Dikey kırp' => array('name' => 'crop_y', 'desc' => 'Dikey olarak resmi kırpar, yüzde cinsindendir. (0-100)', 'type' => 'number'),
                    'Zorunlu yatay genişlik' => array('name' => 'width', 'desc' => 'Her resim yatay olarak bu boyuta getirilir, pixel cinsindendir.', 'type' => 'number'),
                    'Zorunlu dikey genişlik' => array('name' => 'height', 'desc' => 'Her resim dikey olarak bu boyuta getirilir,  pixel cinsindendir.', 'type' => 'number'),
                    );

                $form_search  = array(
                    'Kaç adet resim' => array('name' => 'count', 'desc' => 'Google, instagram veya pinterest\'ten kaç resim çekilmeli.', 'type' => 'number'),
                    'Resim boyutları' => array('name' => 'imgsz', 'desc' => 'Sadece belirtilen boyutlar için arama yapar', 'options' => $image_sizes),
                    'Kaçıncı sayfadan' => array('name' => 'start_index', 'desc' => 'Aramaya kaçıncı sayfadan başlanmalı, sadece Google için geçerlidir.', 'type' => 'number'),
                    );

                $form_auto  = array(
                    'Editor Modu' => array('name' => 'editor_mode', 'desc' => 'Resimleri eklerken daha detaylı bilgi ekleyebilirsiniz.', 'options' => $modes),
                    'Resimler için başlık' => array('name' => 'auto_tag', 'desc' => 'Resimler için caption kodu ekler.', 'type' => 'checkbox'),
                    'Resimler için açıklama' => array('name' => 'imagets_desc', 'desc' => 'Resimler için açıklamaları da wordpress\'e kaydeder.', 'type' => 'checkbox'),
                    'Önizleme için proxy' => array('name' => 'thumb_proxy', 'desc' => 'Bazen önizlemeler doğru görüntülenmeyebilir, proxy ekleyebilirsiniz.', 'type' => 'checkbox'),
                    'Arama Önerileri' => array('name' => 'imagets_suggestion', 'desc' => 'Aramalar altında öneri verir.', 'type' => 'checkbox'),
                    'Dil Seçeneği' => array('name' => 'imagets_language', 'desc' => 'Aramayı belirtilen dilde yapar.', 'options' => $langs),
                    );

                $label = array(
                    'Lisans Ayarları' => $form_licence,
                    'Uygulanacak özellikler' => $form_images,
                    'Logo ayarları' => $form_watermark,
                    'Çerçeve ayarları' => $form_border,
                    'Boyutlandırma' => $form_scale,
                    'Arama ayarları' => $form_search,
                    'Yapılandırma' => $form_auto,
                    );

                foreach ($label as $key => $form) {
                    echo '<tr valign="top"><th class="title"><h3>'.$key.'</h3></th><td></td></tr>';
                    foreach ($form as $key => $value) {

                        if(@$value['options']){
                            echo '<tr valign="top">
                                  <th class="title">'.$key.'</th>
                                  <td class="addon column-addon"><select name="'.$value['name'].'">
                                  <option selected="selected" disabled="disabled" value="">Seçin :</option>';

                                    foreach ($value['options'] as $key => $option) {
                                        $select = get_option( $value['name'] ) == $option ? 'selected' : '';
                                        echo '<option '.$select.' value='.$option.'>'.$key.'</option>';
                                    }

                            echo '</select> <small>'.$value['desc'].'</small></td>
                                  </tr>';
                        }elseif(@$value['type'] == 'checkbox'){
                            $checked = get_option( $value['name'] ) == 'true' ? 'checked' : '';
                            echo '<tr valign="top">
                                  <th class="title">'.$key.'</th>
                                  <td><input type="checkbox" name="'.$value['name'].'" '.$checked.' value="true"/> <small>'.$value['desc'].'</small></td>
                                  </tr>';
                        }elseif(@$value['type'] == 'color'){
                            echo '<tr valign="top">
                                  <th class="title">'.$key.'</th>
                                  <td><input type="color" name="'.$value['name'].'" value="'.get_option( $value['name'] ).'"/> <small>'.$value['desc'].'</small></td>
                                  </tr>';
                        }elseif(@$value['type'] == 'number'){
                            echo '<tr valign="top">
                                  <th class="title">'.$key.'</th>
                                  <td><input type="number" name="'.$value['name'].'" value="'.get_option( $value['name'] ).'"/> <small>'.$value['desc'].'</small></td>
                                  </tr>';
                        }elseif(@$value['type'] == 'range'){
                            echo '<tr valign="top">
                                  <th class="title">'.$key.'</th>
                                  <td><input type="range" min="1" max="100" step="any" name="'.$value['name'].'" value="'.get_option( $value['name'] ).'"/> <small>'.$value['desc'].'</small></td>
                                  </tr>';
                        }else{
                            echo '<tr valign="top">
                                  <th class="title">'.$key.'</th>
                                  <td><input type="text" name="'.$value['name'].'" value="'.get_option( $value['name'] ).'"/> <small>'.$value['desc'].'</small></td>
                                  </tr>';
                        }
                        
                    }
                }

            echo '</table>';
            submit_button();
            echo '</form>';

        }
    }
}