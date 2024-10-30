<?php
if ( ! defined( 'ABSPATH' ) ) exit;
add_thickbox();
?>
<div id="imagets_thickbox">
  <div ng-app="Core">
  <script>
  <?php
      echo 'var licence = "'.$licence.'";';
      echo 'var jscript = "'.$plugin_url.'";';
      echo 'var post_id = "'.@$post_id.'";';
      echo 'var ajaxurl = "'.admin_url( 'admin-ajax.php' ).'";';
      echo 'var option_count = parseInt('.$option_count.');';
      echo 'var option_imgsz = "'.$option_imgsz.'";';
      echo 'var option_start_index = parseInt('.$option_start_index.');';
      echo 'var editor_mode = "'.$option_editor_mode.'";';
      echo 'var imagets_language = "'.$imagets_language.'";';
      echo 'var imagets_desc = "'.get_option('imagets_desc').'";';
  ?>
  </script>
  <div ng-controller="Controller" class="manager" id="imagets_manager">

    <div class="overflow">
      <div class="previewBig" id="scanPage" style="display:none">
        <img src="<?php echo $this->plugin_url; ?>assets/images/scan.gif">
      </div>

      <img id="logo" src="<?php echo $this->plugin_url; ?>assets/images/logo.png">

      <ul class="nav nav-tabs">
          <li ng-class="{'active' : form.tab == 'search'}" ng-click="form.tab = 'search'"><a href="javascript:void(0)"><span class="glyphicon glyphicon-search"></span></a></li>
          <li ng-class="{'active' : form.tab == 'link'}"   ng-click="form.tab = 'link'"  ><a href="javascript:void(0)"><span class="glyphicon glyphicon-picture"></span></a></li>
          <li ng-class="{'active' : form.tab == 'fetch'}"  ng-click="form.tab = 'fetch'" ><a href="javascript:void(0)"><span class="glyphicon glyphicon-magnet"></span></a></li>
      </ul>

      <form ng-show="form.tab == 'search'" ng-submit="search()">
        <p>Kelime ile belli siteler üzerinde arama yapabilir ve fotoğrafları sıralayabilirsiniz.</p>
        <div class="input-group">
          <input type="text" class="form-control" ng-model="form.keyword" id="searchFor" placeholder="Search for...">
          <span class="input-group-btn">

              <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Ara</button>
              <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <ul class="dropdown-menu pull-right">
                <li><a href="javascript:void(0)">Resim boyutu</a></li>
                <li class="divider"></li>
                <li ng-class="{'active' : form.size == 'xxlarge'}" ng-click="form.size = 'xxlarge'"><a href="javascript:void(0)">Çok büyük</a></li>
                <li ng-class="{'active' : form.size == 'large'}" ng-click="form.size = 'large'"><a href="javascript:void(0)">Büyük</a></li>
                <li ng-class="{'active' : form.size == 'medium'}" ng-click="form.size = 'medium'"><a href="javascript:void(0)">Orta</a></li>
                <li ng-class="{'active' : form.size == 'small'}" ng-click="form.size = 'small'"><a href="javascript:void(0)">Küçük</a></li>
              </ul>
            
          </span>
        </div>

        <ul class="list-group" id="suggestion" style="margin-top:15px;display:none">
        </ul>
        <br>
        <div>
          <?php
            $attachments = get_children(
              array(
                'post_type' => 'attachment',
                'post_parent' => $post_id
              )
            );

            $gallery = array();

            foreach ($attachments as $attachment) {
              $gallery[] = array('tbUrl' => $attachment->guid, 'url' => $attachment->guid);
            }

            echo '<script>var gallery = '.json_encode($gallery).';</script>';
          ?>
          <div ng-repeat="option in element" class="btn-group btn-group-xs">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
              {{option.name}} : <b>{{option.options[option.selected].name}}</b> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
              <li ng-repeat="item in option.options" ng-click="option.selected = $index" ng-class="{'active' : $index == option.selected}"><a href="javascript:void(0)">{{item.name}}</a></li>
            </ul>
          </div>
        </div>
      </form>

      <form ng-show="form.tab == 'link'" ng-submit="getLinks()">
        <p>Resimlerin linklerini buraya yapıştırın.</p>
        <textarea ng-model="form.links" class="form-control" rows="4" placeholder="http://example.com/sample.jpg
http://example.com/example.jpg..."></textarea>
        <br>
        <p align="right">
          <small class="pull-left">Kabul edilen formatlar : (JPG, PNG, GIF). Lütfen linkleri alt alt yazınız.</small>
          <button type="submit" class="btn btn-primary">Getir</button>
        </p>
      </form>

      <form ng-show="form.tab == 'fetch'" ng-submit="fetchPage()">
        <p>Bir blog yazısının içerisinden veya herhangi bir sayfadaki tüm resimleri çeker.</p>
        <div class="input-group">
          <input type="text" class="form-control" ng-model="form.page" placeholder="http://example.com/test-page">
          <span class="input-group-btn">
              <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-magnet"></span> Getir</button>
          </span>
        </div>
      </form>

      <div class="previewBig" ng-show="bigImage" ng-click="bigImage = ''">
        <img ng-src="{{bigImage}}">
      </div>

      <div class="images" ng-if="type == 'normal'">
        <h1 class="nothing" ng-if="images.length<1"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Resim arama ile başlayın</h1>
        <div class="item" ng-mousedown="setFeatured(image)" ng-mouseup="cancelPress();" ng-class="{'featured' : isFeatured(image) ,'active' : isSelected(image) > -1}" ng-click="selectObject(image)" ng-repeat="image in images" ng-style="{'background-image' : 'url(' + image.tbUrl + ')'}"><input ng-click="selectObject(image, true)" class="mini-textbox" ng-model="image.name" ng-init="image.name = basename(image.url); loadedImage(image.url, image)" type="text" ng-value="basename(image.url)"/>
        <i class="close_btn glyphicon glyphicon-ok"></i><i class="full_btn glyphicon glyphicon-fullscreen" ng-click="goLink(image.url);"></i>
        <span class="size">{{image.width}}X{{image.height}}</span>
        </div>
      </div>

      <div id="post-wrapper" ng-if="type == 'pro'">
        <h1 class="nothing" ng-if="images.length<1"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Resim arama ile başlayın</h1>
        <li class="post" ng-mousedown="setFeatured(image)" ng-mouseup="cancelPress();" ng-class="{'active' : isSelected(image) > -1, 'error' : image.error}" ng-click="selectObject(image)" ng-repeat="image in images">
          <div class="thumb" ng-style="{'background-image' : 'url({{urlFix(image.tbUrl)}})'}">
            <i class="close_btn glyphicon glyphicon-ok"></i>
            <span class="size">{{image.width}}X{{image.height}}</span>
          </div>

          <div ng-click="selectObject(image, true)" class="information">
            <div class="form-group">
              <div class="input-group marginBottom">
                <div class="input-group-addon">Başlık</div>
                <input class="form-control" ng-model="image.name" ng-init="image.name = basename(image.url);  loadedImage(image.url, image)" type="text" placeholder="Başlık" ng-value="basename(image.url)"/>
              </div>
            </div>
            <textarea ng-model="image.contentNoFormatting" ng-init="image.contentNoFormatting = clearIfDisabled(image.contentNoFormatting); " class="form-control form-text marginBottom">Content</textarea>
            <input ng-model="image.tag" class="form-control input-sm pull-left" style="width:50%;" type="text" placeholder="Etiketler"/>

            <select ng-model="image.category" class="form-control input-sm pull-right" style="width:calc(50% - 10px)"> 
             <option value=""><?php echo esc_attr(__('Kategory Seçin')); ?></option> 
             <?php
              $categories = get_categories('hide_empty=0'); 
              foreach ($categories as $category) {
                $option = '<option value="'.$category->cat_ID.'">';
                $option .= $category->cat_name;
                $option .= '</option>';
                echo $option;
              }
             ?>
            </select>
          </div>
        </li>
      </div>
      </div>

      <footer ng-show="form.status == 'loading'">
        <div class="waterImages">
          <div class="mini-picture" ng-repeat="image in selectedImages" ng-style="{'background-image' : 'url({{urlFix(image.tbUrl)}})'}">
            <div class="water"></div>
          </div>
        </div>

      </footer>

      <footer ng-show="form.status == 'home'">

        <button class="btn btn-success" ng-click="download('gallery')"><img class="loading gallery" src="<?php echo $this->plugin_url; ?>assets/images/loading.gif"> <span class="glyphicon glyphicon-th" aria-hidden="true"></span> Galeri olarak ekle</button>
        <button class="btn btn-primary" ng-click="download('append')"><img class="loading append" src="<?php echo $this->plugin_url; ?>assets/images/loading.gif"> <span class="glyphicon glyphicon-picture" aria-hidden="true"></span> Yazı sonuna ekle</button>


        <span class="pull-right">
          <a style="margin-right:10px;font-weight:normal;" class="ajax-link">{{selectedImages.length}} adet</a>
          <button class="btn btn-default" ng-click="clear()"><span class="glyphicon glyphicon-trash"></span> Temizle</button>

          <div class="btn-group dropup">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
              <img class="loading gallery" src="<?php echo $this->plugin_url; ?>assets/images/loading.gif"> <span class="glyphicon glyphicon-cog"></span> İşlemler <span class="caret"></span>
            </button>
            <ul class="dropdown-menu pull-right" role="menu">
              <li><a href="http://imagets.com" target="_blank" class="ajax-link">{{form.digit}} <span class="glyphicon glyphicon-time pull-right"></span></a></li>
              <li><a href="javascript:void(0)"><b>Dosya</b></a></li>
              <li class="divider"></li>
              <li><a href="javascript:void(0)" ng-click="setByTitle();"><span class="glyphicon glyphicon-pencil"></span> İsimleri başlığa göre ayarla</a></li>
              <li><a href="javascript:void(0)" ng-click="download('not_download');"><span class="glyphicon glyphicon-share"></span> Dosyaları indirmeden ekle</a></li>
              <li><a href="javascript:void(0)" ng-click="download('only_download');"><span class="glyphicon glyphicon-floppy-save"></span> Sadece sunucuya indir</a></li>
              <li class="divider"></li>
              <li><a href="javascript:void(0)"><b>Seçim</b></a></li>
              <li class="divider"></li>
              <li><a href="javascript:void(0)" ng-click="selectAllImages();">Tüm resimleri seç</a></li>
            </ul>
          </div>
        </span>
      </footer>

  </div>
  </div>
</div>