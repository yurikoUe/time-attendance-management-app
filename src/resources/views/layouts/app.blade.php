<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Management</title>
  <link rel="stylesheet" href="{{ asset('css/common/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common/layout.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common/message.css') }}">
  @yield('css')
</head>

<body>
  <header class="header">
    <div class="header__inner">
      <div class="header-utilities">
        

        <div class="header__logo">
          <img src="{{ asset('images/logo.svg') }}" alt="サイトロゴ">
        </div>

        @php
          $user = Auth::guard('web')->user();
          $admin = Auth::guard('admin')->user();
        @endphp

        <nav>
          @if ($admin)
            {{-- 管理者メニュー --}}
            <ul class="header-nav">
              <li class="header-nav__item">
                <a class="header-nav__link" href="{{ route('admin.attendance.index') }}">勤怠一覧</a>
              </li>
              <li class="header-nav__item">
                <a class="header-nav__link" href="{{ route('staff.list') }}">スタッフ一覧</a>
              </li>
              <li class="header-nav__item">
                <a class="header-nav__link" href="{{ route('attendance-request.index') }}">申請一覧</a>
              </li>
              <li class="header-nav__item">
                <form class="form" action="/logout" method="post">
                  @csrf
                  <button class="header-nav__button">ログアウト</button>
                </form>
              </li>
            </ul>
          @elseif ($user)
            {{-- 一般ユーザーメニュー --}}
            <ul class="header-nav">
              <li class="header-nav__item">
                <a class="header-nav__link" href="/attendance">勤怠</a>
              </li>
              <li class="header-nav__item">
                <a class="header-nav__link" href="/attendance/list">勤怠一覧</a>
              </li>
              <li class="header-nav__item">
                <a class="header-nav__link" href="/stamp_correction_request/list">申請</a>
              </li>
              <li class="header-nav__item">
                <form class="form" action="/logout" method="post">
                  @csrf
                  <button class="header-nav__button">ログアウト</button>
                </form>
              </li>
            </ul>
          @endif
        </nav>
        
      </div>
    </div>
  </header>

  <main>
    @yield('content')
  </main>
</body>

</html>
