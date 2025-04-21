@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common/requests-index.css') }}">
@endsection

@section('content')

<div class="attendance-requests">
    <h1 class="attendance-requests__title">申請一覧</h1>

    <!-- タブ切り替え -->
    <div class="attendance-requests__tabs">
        <div class="attendance-requests__tab">
            <a class="attendance-requests__tab-link {{ $status === 'waiting' ? 'attendance-requests__tab-link--active' : '' }}" href="{{ route('attendance-request.index', ['status' => 'waiting']) }}">承認待ち</a>
        </div>
        <div class="attendance-requests__tab">
            <a class="attendance-requests__tab-link {{ $status === 'approved' ? 'attendance-requests__tab-link--active' : '' }}" href="{{ route('attendance-request.index', ['status' => 'approved']) }}">承認済み</a>
        </div>
    </div>
    
    <!-- テーブル表示 -->
    <table class="attendance-requests__table">
        <tr class="attendance-requests__table--header">
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>
        @foreach($requests as $request)
            <tr>
                <td>{{ $request->status->name }}</td>
                <td>{{ $request->attendance->user->name }}</td>
                <td>{{ $request->attendance->work_date->format('Y/m/d') }}</td>
                <td>{{ $request->request_reason }}</td>
                <td>{{ $request->created_at->format('Y/m/d') }}</td>
                <td>
                    <a a class="attendance-requests__detail-link" href="{{ route('attendance-detail.show', $request->attendance_id) }}">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>

@endsection
