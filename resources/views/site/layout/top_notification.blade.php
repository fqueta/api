@if(isset($notification['unread']) && is_object($notification['unread']))
<div class="table-responsive" style="max-height: 450px">
    <table class="table table-hover">
        <tbody>
            @foreach ($notification['unread'] as $k=>$notification)
                @php
                    $link = isset($notification['data']['dlance']['link'])?$notification['data']['dlance']['link'] : @$notification['data']['config']['dleilao']['link_leilao'];
                    $link_thumbnail = isset($notification['data']['dlance']['link_thumbnail'])?$notification['data']['dlance']['link_thumbnail'] : @$notification['data']['config']['dleilao']['link_thumbnail'];
                @endphp
                <tr id="tr-{{@$notification['id']}}">
                    <td class="w-25">
                        <a href="{{$link}}">
                            <img class="w-100" src="{{$link_thumbnail}}" />
                        </a>
                    </td>
                    <td>
                        {{-- @php
                            $dta = explode(" ", $notification['created_at']);
                        @endphp --}}
                        {!!@$notification['data']['message']!!}
                        <br>
                        <small>
                            {!!\Carbon\Carbon::parse(@$notification['created_at'])->format('j F, Y H:m')!!}
                        </small>
                                            </td>
                    <td class="text-right">
                        <button type="button" class="btn btn-light" onclick="markAsRead('{{@$notification['id']}}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>

            @endforeach
        </tbody>
    </table>
</div>
@endif
