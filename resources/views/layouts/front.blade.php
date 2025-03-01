<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="description" content="GeniusCart-New - Multivendor Ecommerce system">
  <meta name="author" content="GeniusOcean">

  @if(isset($page->meta_tag) && isset($page->meta_description))

  <meta name="keywords" content="{{ $page->meta_tag }}">
  <meta name="description" content="{{ $page->meta_description }}">
  <title>{{$gs->title}}</title>

  @elseif(isset($blog->meta_tag) && isset($blog->meta_description))

  <meta property="og:title" content="{{$blog->title}}" />
  <meta property="og:description"
    content="{{ $blog->meta_description != null ? $blog->meta_description : strip_tags($blog->meta_description) }}" />
  <meta property="og:image" content="{{asset('assets/images/blogs/'.$blog->photo)}}" />
  <meta name="keywords" content="{{ $blog->meta_tag }}">
  <meta name="description" content="{{ $blog->meta_description }}">
  <title>{{$gs->title}}</title>

  @elseif(isset($productt))

  <meta name="keywords" content="{{ !empty($productt->meta_tag) ? implode(',', $productt->meta_tag ): '' }}">
  <meta name="description"
    content="{{ $productt->meta_description != null ? $productt->meta_description : strip_tags($productt->description) }}">
  <meta property="og:title" content="{{$productt->name}}" />
  <meta property="og:description"
    content="{{ $productt->meta_description != null ? $productt->meta_description : strip_tags($productt->description) }}" />
  <meta property="og:image" content="{{asset('assets/images/thumbnails/'.$productt->thumbnail)}}" />
  <meta name="author" content="GeniusOcean">
  <title>{{substr($productt->name, 0,11)."-"}}{{$gs->title}}</title>

  @else

  <meta property="og:title" content="{{$gs->title}}" />
  <meta property="og:image" content="{{asset('assets/images/'.$gs->logo)}}" />
  <meta name="keywords" content="{{ $seo->meta_keys }}">
  <meta name="author" content="GeniusOcean">
  <title>{{$gs->title}}</title>

  @endif

  <link rel="icon" type="image/x-icon" href="{{asset('assets/images/'.$gs->favicon)}}" />
  <!-- Google Font -->
  @if ($default_font->font_value)
  <link
    href="https://fonts.googleapis.com/css?family={{ $default_font->font_value }}:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
    rel="stylesheet">
  @else
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@100;200;300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">
  @endif

  <link rel="stylesheet"
    href="{{ asset('assets/front/css/styles.php?color='.str_replace('#','', $gs->colors).'&header_color='.$gs->header_color) }}">
  <link rel="stylesheet" href="{{ asset('assets/front/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/front/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/front/css/plugin.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/front/css/animate.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/front/webfonts/flaticon/flaticon.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/front/css/owl.carousel.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/front/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/front/css/template.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/front/css/style.css') }}">

  <link rel="stylesheet" href="{{ asset('assets/front/css/category/default.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/front/css/toastr.min.css') }}">
  @if ($default_font->font_family)
  <link rel="stylesheet" id="colorr"
    href="{{ asset('assets/front/css/font.php?font_familly='.$default_font->font_family) }}">
  @else
  <link rel="stylesheet" id="colorr" href="{{ asset('assets/front/css/font.php?font_familly='." Open Sans") }}">
  @endif

  @if(!empty($seo->google_analytics))
  <script>
    window.dataLayer = window.dataLayer || [];
		function gtag() {
				dataLayer.push(arguments);
		}
		gtag('js', new Date());
		gtag('config', '{{ $seo->google_analytics }}');
  </script>
  @endif
  @if(!empty($seo->facebook_pixel))
  <script>
    !function(f,b,e,v,n,t,s)
			{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
			n.callMethod.apply(n,arguments):n.queue.push(arguments)};
			if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
			n.queue=[];t=b.createElement(e);t.async=!0;
			t.src=v;s=b.getElementsByTagName(e)[0];
			s.parentNode.insertBefore(t,s)}(window, document,'script',
			'https://connect.facebook.net/en_US/fbevents.js');
			fbq('init', '{{ $seo->facebook_pixel }}');
			fbq('track', 'PageView');
  </script>
  <noscript>
    <img height="1" width="1" style="display:none"
      src="https://www.facebook.com/tr?id={{ $seo->facebook_pixel }}&ev=PageView&noscript=1" />
  </noscript>
  @endif


  @yield('css')
</head>

<body>

  <div id="page_wrapper" class="bg-white">
    <div class="loader">
      <div class="spinner"></div>
    </div>

    @yield('content')

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h3 class="modal-title text-center" id="exampleModalLabel">You May Like </h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><button>
          </div>
          <div class="modal-body" id="cross__product__show">
            
          </div>

        </div>
      </div>
    </div>


  </div>
  <script>
    var mainurl = "{{ url('/') }}";
    var gs      = {!! json_encode(DB::table('generalsettings')->where('id','=',1)->first(['is_loader','decimal_separator','thousand_separator','is_cookie','is_talkto','talkto'])) !!};
    var ps_category = {{ $ps->category }};

    var lang = {
        'days': '{{ __('Days') }}',
        'hrs': '{{ __('Hrs') }}',
        'min': '{{ __('Min') }}',
        'sec': '{{ __('Sec') }}',
        'cart_already': '{{ __('Already Added To Card.') }}',
        'cart_out': '{{ __('Out Of Stock') }}',
        'cart_success': '{{ __('Successfully Added To Cart.') }}',
        'cart_empty': '{{ __('Cart is empty.') }}',
        'coupon_found': '{{ __('Coupon Found.') }}',
        'no_coupon': '{{ __('No Coupon Found.') }}',
        'already_coupon': '{{ __('Coupon Already Applied.') }}',
        'enter_coupon': '{{ __('Enter Coupon First') }}',
        'minimum_qty_error': '{{ __('Minimum Quantity is:') }}',
        'affiliate_link_copy': '{{ __('Affiliate Link Copied Successfully') }}'
    };

  </script>
  <!-- Include Scripts -->
  <script src="{{ asset('assets/front/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/front/js/jquery-ui.min.js') }}"></script>

  <script src="{{ asset('assets/front/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/front/js/popper.min.js') }}"></script>
  <script src="{{ asset('assets/front/js/plugin.js') }}"></script>
  <script src="{{ asset('assets/front/js/waypoint.js') }}"></script>
  <script src="{{ asset('assets/front/js/owl.carousel.min.js') }}"></script>
  <script src="{{ asset('assets/front/js/wow.js')}}"></script>
  <script src="{{ asset('assets/front/js/jquery.countdown.js') }}"></script>
  @yield('zoom')
  <script src="{{ asset('assets/front/js/paraxify.js') }}"></script>
  <script src="{{ asset('assets/front/js/select2.min.js') }}"></script>


  <script src="{{ asset('assets/front/js/toastr.min.js') }}"></script>
  <script src="{{ asset('assets/front/js/custom.js') }}"></script>
  <script src="{{ asset('assets/front/js/main.js') }}"></script>



  <script>
    $(document).ready(function() {
        $('.select2-js-init').select2({minimumResultsForSearch: Infinity});
        $('.select2-js-search-init').select2();
    });


        $(document).on("submit",".subscribeform", function (e) {
      var $this = $(this);
      e.preventDefault();
      $this.find("input").prop("readonly", true);
      $this.find("button").prop("disabled", true);
      $("#sub-btn").prop("disabled", true);
      $.ajax({
        method: "POST",
        url: $(this).prop("action"),
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
          if (data.errors) {
            for (var error in data.errors) {
              toastr.error(data.errors[error]);
            }
          } else {
            toastr.success(data);
            $this.find("input[name=email]").val("");
          }
          $this.find("input").prop("readonly", false);
          $this.find("button").prop("disabled", false);
        },
      });
    });
  </script>



  @php
  echo Toastr::message();

  if(Session::has('success')){
  echo '<script>
    toastr.success("'.Session::get('success').'")
  </script>';
  }

  @endphp
  @yield('script')
</body>

</html>