@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common/attendance-index.css') }}">
@endsection

@section('content')

<div class="attendance__container">
    <h1 class="attendance__title">スタッフ一覧</h1>

    <table class="attendance__table">
        <tr class="attendance__table-header">
            <th class="attendance__table-cell">名前</th>
            <th class="attendance__table-cell">メールアドレス</th>
            <th class="attendance__table-cell">月次勤怠</th>
        </tr>
        @foreach ($staff as $s)
        <tr  class="attendance__table-row">
            <td class="attendance__table-cell">{{ $s->name }}</td>
            <td class="attendance__table-cell">{{ $s->email }}</td>
            <td class="attendance__table-cell">
                <a href="{{ route('staff.attendance', ['id' => $s->id]) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>

@endsection
