<?php
session_start();
if(!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'admin'){
    header('location:login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
	
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="assets/js/script.js"></script>
  <title>AutoSched</title>
 	

<?php
 include('./header.php'); 
 // include('./auth.php'); 
 ?>

</head>
<style>
	body{
        background: #80808045;
  }
  .modal-dialog.large {
    width: 80% !important;
    max-width: unset;
  }
  .modal-dialog.mid-large {
    width: 50% !important;
    max-width: unset;
  }
  #viewer_modal .btn-close {
    position: absolute;
    z-index: 999999;
    /*right: -4.5em;*/
    background: unset;
    color: white;
    border: unset;
    font-size: 27px;
    top: 0;
}
#viewer_modal .modal-dialog {
        width: 80%;
    max-width: unset;
    height: calc(90%);
    max-height: unset;
}
  #viewer_modal .modal-content {
       background: black;
    border: unset;
    height: calc(100%);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  #viewer_modal img,#viewer_modal video{
    max-height: calc(100%);
    max-width: calc(100%);
  }
  
  /* Mobile responsiveness */
  @media (max-width: 768px) {
    #view-panel {
      width: 100%;
      padding: 15px;
      margin-left: 0;
      transition: all 0.3s;
    }
    
    body.sidebar-active #view-panel {
      filter: blur(3px);
      pointer-events: none;
    }
    
    .modal-dialog.large, .modal-dialog.mid-large {
      width: 95% !important;
      max-width: 95% !important;
      margin: 0.5rem auto;
    }
  }
</style>

<body>
	<?php include 'topbar.php' ?>
	<?php include 'navbar.php' ?>
  <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body text-white">
    </div>
  </div>
  <main id="view-panel" >
      <?php $page = isset($_GET['page']) ? $_GET['page'] :'home'; ?>
      <?php 
      if(!file_exists($page.'.php') && !is_dir($page)){
        include '404.php';
      }else{
        if(is_dir($page))
          include $page.'/index.php';
        else
          include $page.'.php';
      }
      ?>
</main>

  <div id="preloader"></div>
  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

  <!-- Mobile scroll-to-top button -->
  <button id="mobile-scroll-top" class="d-md-none btn btn-primary rounded-circle position-fixed" style="bottom: 20px; right: 20px; width: 45px; height: 45px; display: none; z-index: 1030;">
    <i class="fa fa-arrow-up"></i>
  </button>

  <div class="modal fade" id="uni_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title"></h5>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="confirm_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title">Confirmation</h5>
      </div>
      <div class="modal-body">
        <div id="delete_content"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="viewer_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
              <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
              <img src="" alt="">
      </div>
    </div>
  </div>
</body>
<script>
	 window.start_load = function(){
    $('body').prepend('<di id="preloader2"></di>')
  }
  window.end_load = function(){
    $('#preloader2').fadeOut('fast', function() {
        $(this).remove();
      })
  }
 window.viewer_modal = function($src = ''){
    start_load()
    var t = $src.split('.')
    t = t[1]
    if(t =='mp4'){
      var view = $("<video src='"+$src+"' controls autoplay></video>")
    }else{
      var view = $("<img src='"+$src+"' />")
    }
    $('#viewer_modal .modal-content video,#viewer_modal .modal-content img').remove()
    $('#viewer_modal .modal-content').append(view)
    $('#viewer_modal').modal({
            show:true,
            backdrop:'static',
            keyboard:false,
            focus:true
          })
          end_load()  

}
  window.uni_modal = function(title, url, size = '') {
    $.ajax({
        url: url,
        error: function(xhr, status, error) {
            console.error('Modal error:', error);
            console.error('Status:', status);
            alert("An error occurred while loading content");
        },
        success: function(html) {
            try {
                if (!html || typeof html !== 'string') {
                    console.error('Invalid HTML content:', html);
                    alert("Invalid content received");
                    return;
                }
                
                // Make sure modal elements exist
                if (!$('#uni_modal').length) {
                    console.error('Modal element not found');
                    return;
                }
                
                // Set title and content
                $('#uni_modal .modal-title').html(title);
                $('#uni_modal .modal-body').html(html);
                
                // Set size class if specified
                if (size != '') {
                    $('#uni_modal .modal-dialog').removeClass('modal-md').addClass(size);
                } else {
                    $('#uni_modal .modal-dialog').removeClass().addClass("modal-dialog modal-md");
                }
                
                // Show modal
                $('#uni_modal').modal('show');
            } catch (e) {
                console.error('Error showing modal:', e);
                alert("Error displaying content");
            }
        },
        timeout: 10000 // 10 second timeout
    });
}
window._conf = function($msg='',$func='',$params = []){
     $('#confirm_modal #confirm').attr('onclick',$func+"("+$params.join(',')+")")
     $('#confirm_modal .modal-body').html($msg)
     $('#confirm_modal').modal('show')
  }
   window.alert_toast= function($msg = 'TEST',$bg = 'success'){
      $('#alert_toast').removeClass('bg-success')
      $('#alert_toast').removeClass('bg-danger')
      $('#alert_toast').removeClass('bg-info')
      $('#alert_toast').removeClass('bg-warning')

    if($bg == 'success')
      $('#alert_toast').addClass('bg-success')
    if($bg == 'danger')
      $('#alert_toast').addClass('bg-danger')
    if($bg == 'info')
      $('#alert_toast').addClass('bg-info')
    if($bg == 'warning')
      $('#alert_toast').addClass('bg-warning')
    $('#alert_toast .toast-body').html($msg)
    $('#alert_toast').toast({delay:3000}).toast('show');
  }
  $(document).ready(function(){
    $('#preloader').fadeOut('fast', function() {
        $(this).remove();
      })
      
    // Mobile scroll-to-top button
    $(window).scroll(function() {
      if ($(this).scrollTop() > 300) {
        $('#mobile-scroll-top').fadeIn();
      } else {
        $('#mobile-scroll-top').fadeOut();
      }
    });
    
    $('#mobile-scroll-top').click(function() {
      $('html, body').animate({scrollTop: 0}, 300);
      return false;
    });
    
    // Make tables responsive on mobile
    $('.table').addClass('table-responsive');
    
    // Preserve scroll position check
    if (window.location.hash === '#preserve-scroll') {
        const currentPage = getCurrentPage();
        const savedPosition = localStorage.getItem(currentPage + '_scroll_pos');
        
        if (savedPosition) {
            setTimeout(function() {
                window.scrollTo(0, parseInt(savedPosition));
            }, 200);
        }
        
        // Clean URL by removing hash
        if (history.pushState) {
            history.pushState(null, null, window.location.pathname + window.location.search);
        }
    }
    
    // Save scroll position periodically
    const currentPage = getCurrentPage();
    setInterval(function() {
        localStorage.setItem(currentPage + '_scroll_pos', window.scrollY);
    }, 1000);
    
    // Helper function to get current page
    function getCurrentPage() {
        const match = window.location.search.match(/page=([^&]*)/);
        return match ? match[1] : 'home';
    }
  })
  $('.datetimepicker').datetimepicker({
      format:'Y/m/d H:i',
      startDate: '+3d'
  })
  $('.select2').select2({
    placeholder:"Please select here",
    width: "100%"
  })
</script>	
</html>