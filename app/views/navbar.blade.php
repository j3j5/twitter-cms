<!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/"> <i class="fa fa-twitter"></i> {{{ Config::get('app.project_name') }}}</a>
        </div>
        <div class="navbar-collapse collapse">
			<!--    This is for navigation on the left      -->
<!--           <ul class="nav navbar-nav"> -->
<!--             <li class="active"><a href="#">Home</a></li> -->
<!--             <li><a href="/about">About</a></li> -->
<!--             <li><a href="/contact">Contact</a></li> -->
<!--           </ul> -->
          <ul class="nav navbar-nav navbar-right">
			@if(!Auth::check())
            <li><a class="btn btn-small btn-navbar" href="auth/login">Sign in</a></li>
            <li><a class="btn btn-small btn-navbar" href="auth/register">Sign up</a></li>
			@else
			<li><a class="btn btn-small btn-navbar" href="auth/logout">Sign out</a></li>
			@endif
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
