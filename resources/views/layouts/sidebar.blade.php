<nav class="navbar-default navbar-static-side" role="navigation">
   <div class="sidebar-collapse">
       <ul class="nav metismenu" id="side-menu">
           <li class="nav-header">
               <div class="dropdown profile-element"> <span>
                       <img alt="image" class="img-circle" src="{!! asset('asset/img/profile_small.jpg') !!}" />
                        </span>
          <?php /*    <!--     <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                       <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold">Leadership Presence I</strong>
                        </span> <span class="text-muted text-xs block">Admin <b class="caret"></b></span> </span> </a>
                     <ul class="dropdown-menu animated fadeInRight m-t-xs">
                         <li><a href="{{route('admin::profile')}}">Profile</a></li>
                         <li class="divider"></li>
                         <li><a href="{{route('admin::logout')}}">Logout</a></li>
                     </ul> --> */ ?>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                       <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?= env('ADMIN_PROJECT_NAME');?> Admin</strong>
                       </a>
                     
               </div>
               <div class="logo-element">
                   <?= env('ADMIN_PROJECT_SHORT_NAME');?>+
               </div>
           </li>
<!--               
          <li class="{{ $dashboardTab or '' }}">
               <a href="{{route('admin::dashboard')}}"><i class="fa fa-th-large"></i> <span class="nav-label">Dashboards</span></a>
           </li> -->
       <?php /*        
           <li class="{{ $launchTab or '' }}">
               <a href="{{route('admin::launchList')}}"><i class="fa fa-users"></i> <span class="nav-label">Launch Contest</span></a>
           </li>
           
           <li class="{{ $weekTab or '' }}">
               <a href="{{route('admin::weekList')}}"><i class="fa fa-users"></i> <span class="nav-label">Weekly Contest</span></a>
           </li>
           
           <li class="{{ $referralTab or '' }}">
               <a href="{{route('admin::referralList')}}"><i class="fa fa-user"></i> <span class="nav-label">Referrals</span></a>
           </li>
           
           <li class="{{ $locationTab or '' }}">
               <a href="{{route('admin::locationList')}}"><i class="fa fa-university"></i> <span class="nav-label">Locations</span></a>
           </li>
           */ ?>
           <li class="{{ $questionTab or '' }}">
               <a href="{{route('admin::questionList')}}"><i class="fa fa-adn"></i> <span class="nav-label">Question</span></a>
           </li>
           
           <li class="{{ $changePasswordTab or '' }}">
               <a href="{{route('admin::changePassword')}}"><i class="fa fa-cog"></i> <span class="nav-label">Change Password</span></a>
           </li>
       </ul>

   </div>
</nav>