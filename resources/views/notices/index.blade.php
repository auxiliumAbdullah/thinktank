
@extends('layouts.app')

@php
$addNoticePermission = user()->permission('add_notice');
@endphp

@section('content')

    <div class="content-wrapper">
      
    <style>
            .post_wrapper {
                display: flex;
                margin: 35px 0 0;
                flex-wrap: wrap;
                column-gap: 20px;
            }
            .single_post {
                margin:0 0 20px;
                padding: 15px 10px;
                width: 380px;
                border-radius: 4px;
                border: solid 1px #d2d6dc;
                overflow: hidden;
                position: relative;
            }
            .deletePost {
                position: absolute;
                right: 15px;
            }
            .post_social_section {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                align-content: space-around;
                align-items: stretch;
                justify-content: space-between;
            }
            .post_social_section input {
                width: 100%;
                border: solid 1px #d2d6dc;
                border-radius: 20px;
                height: 40px;
            }
            .btn-comment {
                width: 75%;
            }
            .post_top .media-body p {
                margin: 16px 0;
            }
            .single-comment {
                diplay:flex;
            }
            .comments-section {
                border-top: solid 1px #d2d6dc;
                margin: 10px 0 0;
                padding: 10px 0 0;
            }
            .comment-block {
                display: flex;
            }
            .comment-block  p {
                margin: 0;
            }
            .comment-block time {
                font-size:10px;
                margin-left: auto;
            }
        </style>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
                @if ($addNoticePermission == 'all' || $addNoticePermission == 'added')
                    <x-forms.link-primary :link="route('notices.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('modules.notices.addNotice')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>
        @if (Session::has('message'))
        <div class="alert alert-success">
          <ul>
            <li>{{ Session::get('message') }}</li>
          </ul>
        </div>
        @endif
        <div class="post_wrapper">
            @foreach($notice as $nt)
            <div class="single_post">
                <a class="deletePost" href="{{route('notice.delete',$nt->id)}}">Delete</a>
                <div class="post_top">
                    <div class="media">
                      <div class="media-body">
                          <div class="d-flex">
                              <img style="width:30px;height:30px" src="{{asset_url('/').'/avatar/'.user()->image}}" class="mr-3 rounded-circle" alt="...">
                              <h5 class="mt-1 mb-0">{{user()->name}}</h5>
                          </div>
                          {!! $nt->description !!}
                      </div>
                    </div>
                </div>
                <div class="post_social_section align-items-center">
                    <div class="btn-like">
                        <button class="user_reaction_btn {{$nt->likes?->likes==1?'active':''}}">
                            <p class="user_u_reaction"  data-post="{{$nt->id}}"><span>Like</span>&nbsp;<span  class="like-counter">{{ \App\Models\NoticeLike::where('notice_id','=',$nt->id)->count()}}</span></p>
                            <div class="emoji-container" data-post="{{$nt->id}}">
                                <div class="emoji like">
                                    <div class="icon" data-title="Like"></div>
                                </div>
                                <div class="emoji love">
                                    <div class="icon" data-title="Love"></div>
                                </div>
                                <div class="emoji haha">
                                    <div class="icon" data-title="Haha"></div>
                                </div>
                                <div class="emoji wow">
                                    <div class="icon" data-title="Wow"></div>
                                </div>
                                <div class="emoji sad">
                                    <div class="icon" data-title="Sad"></div>
                                </div>
                                <div class="emoji angry">
                                    <div class="icon" data-title="Angry"></div>
                                </div>
                            </div>
                        </button>
                    </div>
                    <div class="btn-comment">
                        <input class="px-3 comment__area" data-post="{{$nt->id}}" type="text" placeholder="Comments">
                    </div>
                </div>
                <div class="comments-section">
                    @foreach($nt->comments as $ct)
                    @php
                    $user_data = \App\Models\User::where('id','=',$ct->user_id)->first()
                    @endphp
                    <div class="comment-block">
                        <img style="width:30px;height:30px" src="{{asset_url('/').'/avatar/'.$user_data->image}}" class="mr-3 rounded-circle" alt="{{$user_data->name}}">
                        <div class="">
                            <b>{{$user_data->name}}</b>
                            <p>{{$ct->comment}}</p>
                        </div>
                        <time>{{$ct->created_at->diffForHumans()}}</time>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
       
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

<script src="{{asset('/vendor/jquery/jquery.min.js')}}"></script>
<script>
    var asset_url = "{{asset('/')}}";
    $(document).on('keypress','.comment__area',function(e) {
        if(e.which == 10 || e.which == 13) {
            $(this).prop('disabled',true);
            var target = $(this);
            if($.trim($(this).val())==""){
                alert('Please enter some words');
            }else{
                $.ajax({
                    url:"{{route('notice.comment')}}",
                    type:"POST",
                    cache:false,
                    data:{"notice_id":$(this).attr('data-post'),"_token":"{{csrf_token()}}",comment:$(this).val()},
                    success:function(response){
                        target.parent().parent().parent().find('.comments-section').append(`
                            <div class="comment-block">
                                <img style="width:30px;height:30px" src="${asset_url}user-uploads/avatar/${response.user.image}" class="mr-3" alt="${response.user.name}">
                                <p>${response.user.name}</p>
                                <p>${response.data.comment}</p>
                            </div>
                        `);
                        target.prop('disabled',false);
                    }
                })
            }
        }
    });
    $(document).on('click','.user_u_reaction',function(){
        var currentNode = $(this).parent(),
        counter_item = currentNode.find('.like-counter');
        if(counter_item.text()>0){
            currentNode.removeClass('active');
            counter_item.text(parseInt(counter_item.text())-1);
            $.ajax({
                url:"{{route('notice.like')}}",
                type:"POST",
                cache:false,
                data:{"notice_id":$(this).attr('data-post'),"_token":"{{csrf_token()}}","is_del":1},
                success:function(response){
                    $('.user_reaction_btn').removeClass('disabled').removeAttr('disabled');
                }
            })
        }
    })
    $(document).on('click','.user_reaction_btn .emoji',function(){
        var currentNode = $(this).parent(),
        counter_item = currentNode.parent().find('.like-counter');
        var is_edit = 0;
        if(currentNode.parent().hasClass('active')){
            is_edit = 1;
        }else{
            is_edit = 0;
            counter_item.text(parseInt(counter_item.text())+1);
            currentNode.parent().addClass('active');
        }
        $('.user_reaction_btn').addClass('disabled').attr('disbaled',true);
        $.ajax({
            url:"{{route('notice.like')}}",
            type:"POST",
            cache:false,
            data:{"notice_id":currentNode.attr('data-post'),"_token":"{{csrf_token()}}",already:is_edit},
            success:function(response){
                $('.user_reaction_btn').removeClass('disabled').removeAttr('disabled');
            }
        })
    });
</script>

    <style>
        button {
  background: transparent;
  padding: 10px 15px;
  outline: none;
  position: relative;
}
button span {
  color: #7f7f7f;
}
button span:not(.like-counter):before {
  content: "";
  display: inline-block;
  height: 14px;
  margin: 0 6px -1px 0;
  width: 14px;
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAQBAMAAADpKDJvAAAAJ1BMVEUAAABLT1ZVWV/q6uvf3+FiZWtbXmWpq66Hio/x8fK+wMKNkJR0d3wR0qQdAAAAAXRSTlMAQObYZgAAAEhJREFUCNdjQAU8DTAWoySMtVAYymBRDICyzAQLoKxGQUEhByCtqKAoKCgGYgkKCAoKijLAWNJwljicJQxhBQoKJjOA9TLgBABj4Ac4SLsYiAAAAABJRU5ErkJggg==");
  background-repeat: no-repeat;
  background-size: auto;
}
.user_reaction_btn.active{
    color:#1d82f5;
}
button .emoji-container {
  opacity: 0;
  position: absolute;
  bottom: 35px;
  left: 20px;
  background: #fff;
  height: 50px;
  width: 290px;
  box-shadow: 0 2px 2px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.05);
  border-radius: 50px;
  padding: 1px;
  box-sizing: border-box;
  transition: opacity 200ms ease;
  pointer-events: none;
}
button:not(.disbaled) .emoji-container:before {
  display: block;
  content: "";
  background: transparent;
  height: 30px;
  position: absolute;
  width: 100%;
  bottom: -15px;
}
button:not(.disbaled)  .emoji-container .emoji {
  width: 48px;
  height: 48px;
  float: left;
}
button .emoji-container .emoji .icon {
  background-image: url("data:image/png;base64,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");
  width: 48px;
  height: 48px;
  float: left;
  transform: scale(0.8, 0.8) translate(0, 0);
  transition: transform 200ms ease;
}
button:not(.disbaled)  .emoji-container .emoji .icon:hover {
  transform: scale(1, 1) translate(0, -6px);
}
button:not(.disbaled)  .emoji-container .emoji .icon:hover:after {
  opacity: 1;
}
button:not(.disbaled)  .emoji-container .emoji .icon:after {
  display: block;
  position: absolute;
  width: 55px;
  bottom: 60px;
  content: attr(data-title);
  background-color: rgba(0, 0, 0, 0.8);
  color: #fff;
  padding: 4px 8px;
  border-radius: 20px;
  opacity: 0;
}
.like .icon {
  background-position: 0 -144px;
}
.love .icon {
  background-position: 0 -192px;
}
.haha .icon {
  background-position: 0 -96px;
}
.wow .icon {
  background-position: 0 -288px;
}
.sad .icon {
  background-position: 0 -240px;
}
button:hover .emoji-container {
  opacity: 1;
  pointer-events: all;
}
button:hover .like {
  -webkit-animation-duration: 0.7333s;
  -webkit-animation-name: head-1-anim;
}
button:hover .like .icon {
  background-position: 0 -144px;
}
button:hover .love {
  -webkit-animation-duration: 0.9833s;
  -webkit-animation-name: head-2-anim;
}
button:hover .love .icon {
  background-position: 0 -192px;
}
button:hover .haha {
  -webkit-animation-duration: 1.0833s;
  -webkit-animation-name: head-3-anim;
}
button:hover .haha .icon {
  background-position: 0 -96px;
}
button:hover .wow {
  -webkit-animation-duration: 0.9333s;
  -webkit-animation-name: head-4-anim;
}
button:hover .wow .icon {
  background-position: 0 -288px;
}
button:hover .sad {
  -webkit-animation-duration: 1.2167s;
  -webkit-animation-name: head-5-anim;
}
button:hover .sad .icon {
  background-position: 0 -240px;
}
button:hover .angry {
  -webkit-animation-duration: 1.2833s;
  -webkit-animation-name: head-6-anim;
}
@-webkit-keyframes head-1-anim {
  0% {
    opacity: 0.3374;
    transform: translateY(35.7785px) scale(0.3374, 0.3374);
  }
  2.2727% {
    opacity: 0.5075;
    transform: translateY(26.5963px) scale(0.5075, 0.5075);
  }
  4.5455% {
    opacity: 0.6569;
    transform: translateY(18.5271px) scale(0.6569, 0.6569);
  }
  6.8182% {
    opacity: 0.7796;
    transform: translateY(11.903px) scale(0.7796, 0.7796);
  }
  9.0909% {
    opacity: 0.8744;
    transform: translateY(6.7839px) scale(0.8744, 0.8744);
  }
  11.3636% {
    opacity: 0.9433;
    transform: translateY(3.0607px) scale(0.9433, 0.9433);
  }
  13.6364% {
    opacity: 0.9901;
    transform: translateY(0.5333px) scale(0.9901, 0.9901);
  }
  15.9091% {
    opacity: 1;
    transform: translateY(-1.0335px) scale(1.0191, 1.0191);
  }
  18.1818% {
    transform: translateY(-1.8733px) scale(1.0347, 1.0347);
  }
  20.4545% {
    transform: translateY(-2.1963px) scale(1.0407, 1.0407);
  }
  22.7273% {
    transform: translateY(-2.1782px) scale(1.0403, 1.0403);
  }
  25.0000% {
    transform: translateY(-1.9574px) scale(1.0362, 1.0362);
  }
  27.2727% {
    transform: translateY(-1.6364px) scale(1.0303, 1.0303);
  }
  29.5455% {
    transform: translateY(-1.2861px) scale(1.0238, 1.0238);
  }
  31.8182% {
    transform: translateY(-0.9522px) scale(1.0176, 1.0176);
  }
  34.0909% {
    transform: translateY(-0.6601px) scale(1.0122, 1.0122);
  }
  36.3636% {
    transform: translateY(-0.4214px) scale(1.0078, 1.0078);
  }
  38.6364% {
    transform: translateY(-0.2376px) scale(1.0044, 1.0044);
  }
  40.9091% {
    transform: translateY(-0.1046px) scale(1.0019, 1.0019);
  }
  43.1818% {
    opacity: 1;
    transform: translateY(-0.0147px) scale(1.0003, 1.0003);
  }
  45.4545% {
    opacity: 0.9992;
    transform: translateY(0.0406px) scale(0.9992, 0.9992);
  }
  47.7273% {
    opacity: 0.9987;
    transform: translateY(0.0699px) scale(0.9987, 0.9987);
  }
  50% {
    opacity: 0.9985;
    transform: translateY(0.0807px) scale(0.9985, 0.9985);
  }
  52.2727% {
    opacity: 0.9985;
    transform: translateY(0.0794px) scale(0.9985, 0.9985);
  }
  54.5455% {
    opacity: 0.9987;
    transform: translateY(0.0709px) scale(0.9987, 0.9987);
  }
  56.8182% {
    opacity: 0.9989;
    transform: translateY(0.059px) scale(0.9989, 0.9989);
  }
  59.0909% {
    opacity: 0.9991;
    transform: translateY(0.0462px) scale(0.9991, 0.9991);
  }
  61.3636% {
    opacity: 0.9994;
    transform: translateY(0.0341px) scale(0.9994, 0.9994);
  }
  63.6364% {
    opacity: 0.9996;
    transform: translateY(0.0235px) scale(0.9996, 0.9996);
  }
  65.9091% {
    opacity: 0.9997;
    transform: translateY(0.0149px) scale(0.9997, 0.9997);
  }
  68.1818% {
    opacity: 0.9998;
    transform: translateY(0.0083px) scale(0.9998, 0.9998);
  }
  70.4545% {
    opacity: 0.9999;
    transform: translateY(0.0036px) scale(0.9999, 0.9999);
  }
  72.7273% {
    opacity: 1;
    transform: translateY(0.0004px) scale(1, 1);
  }
  75.0000% {
    transform: translateY(-0.0016px) scale(1, 1);
  }
  77.2727% {
    transform: translateY(-0.0026px) scale(1, 1);
  }
  79.5455% {
    transform: translateY(-0.003px) scale(1.0001, 1.0001);
  }
  81.8182% {
    transform: translateY(-0.0029px) scale(1.0001, 1.0001);
  }
  84.0909% {
    transform: translateY(-0.0026px) scale(1, 1);
  }
  86.3636% {
    transform: translateY(-0.0021px) scale(1, 1);
  }
  88.6364% {
    transform: translateY(-0.0017px) scale(1, 1);
  }
  90.9091% {
    transform: translateY(-0.0012px) scale(1, 1);
  }
  93.1818% {
    transform: translateY(-0.0008px) scale(1, 1);
  }
  95.4545% {
    transform: translateY(-0.0005px) scale(1, 1);
  }
  97.7273% {
    transform: translateY(-0.0003px) scale(1, 1);
  }
  100% {
    opacity: 1;
    transform: translateY(-0.0001px) scale(1, 1);
  }
}
@-webkit-keyframes head-2-anim {
  0% {
    opacity: 0.0825;
    transform: translateY(49.5448px) scale(0.0825, 0.0825);
  }
  1.6949% {
    opacity: 0.1684;
    transform: translateY(44.9066px) scale(0.1684, 0.1684);
  }
  3.3898% {
    opacity: 0.2765;
    transform: translateY(39.0668px) scale(0.2765, 0.2765);
  }
  5.0847% {
    opacity: 0.3977;
    transform: translateY(32.5232px) scale(0.3977, 0.3977);
  }
  6.7797% {
    opacity: 0.5224;
    transform: translateY(25.7913px) scale(0.5224, 0.5224);
  }
  8.4746% {
    opacity: 0.6421;
    transform: translateY(19.3257px) scale(0.6421, 0.6421);
  }
  10.1695% {
    opacity: 0.7504;
    transform: translateY(13.476px) scale(0.7504, 0.7504);
  }
  11.8644% {
    opacity: 0.8432;
    transform: translateY(8.4697px) scale(0.8432, 0.8432);
  }
  13.5593% {
    opacity: 0.9182;
    transform: translateY(4.4173px) scale(0.9182, 0.9182);
  }
  15.2542% {
    opacity: 0.9754;
    transform: translateY(1.3294px) scale(0.9754, 0.9754);
  }
  16.9492% {
    opacity: 1;
    transform: translateY(-0.859px) scale(1.0159, 1.0159);
  }
  18.6441% {
    transform: translateY(-2.2629px) scale(1.0419, 1.0419);
  }
  20.3390% {
    transform: translateY(-3.0237px) scale(1.056, 1.056);
  }
  22.0339% {
    transform: translateY(-3.29px) scale(1.0609, 1.0609);
  }
  23.7288% {
    transform: translateY(-3.203px) scale(1.0593, 1.0593);
  }
  25.4237% {
    transform: translateY(-2.887px) scale(1.0535, 1.0535);
  }
  27.1186% {
    transform: translateY(-2.4446px) scale(1.0453, 1.0453);
  }
  28.8136% {
    transform: translateY(-1.9549px) scale(1.0362, 1.0362);
  }
  30.5085% {
    transform: translateY(-1.4744px) scale(1.0273, 1.0273);
  }
  32.2034% {
    transform: translateY(-1.0398px) scale(1.0193, 1.0193);
  }
  33.8983% {
    transform: translateY(-0.6716px) scale(1.0124, 1.0124);
  }
  35.5932% {
    transform: translateY(-0.3774px) scale(1.007, 1.007);
  }
  37.2881% {
    transform: translateY(-0.1562px) scale(1.0029, 1.0029);
  }
  38.9831% {
    opacity: 1;
    transform: translateY(-0.001px) scale(1, 1);
  }
  40.6780% {
    opacity: 0.9982;
    transform: translateY(0.0985px) scale(0.9982, 0.9982);
  }
  42.3729% {
    opacity: 0.9972;
    transform: translateY(0.1536px) scale(0.9972, 0.9972);
  }
  44.0678% {
    opacity: 0.9968;
    transform: translateY(0.1754px) scale(0.9968, 0.9968);
  }
  45.7627% {
    opacity: 0.9968;
    transform: translateY(0.1741px) scale(0.9968, 0.9968);
  }
  47.4576% {
    opacity: 0.9971;
    transform: translateY(0.1582px) scale(0.9971, 0.9971);
  }
  49.1525% {
    opacity: 0.9975;
    transform: translateY(0.1342px) scale(0.9975, 0.9975);
  }
  50.8475% {
    opacity: 0.998;
    transform: translateY(0.1073px) scale(0.998, 0.998);
  }
  52.5424% {
    opacity: 0.9985;
    transform: translateY(0.0809px) scale(0.9985, 0.9985);
  }
  54.2373% {
    opacity: 0.9989;
    transform: translateY(0.0571px) scale(0.9989, 0.9989);
  }
  55.9322% {
    opacity: 0.9993;
    transform: translateY(0.037px) scale(0.9993, 0.9993);
  }
  57.6271% {
    opacity: 0.9996;
    transform: translateY(0.0211px) scale(0.9996, 0.9996);
  }
  59.3220% {
    opacity: 0.9998;
    transform: translateY(0.0092px) scale(0.9998, 0.9998);
  }
  61.0169% {
    opacity: 1;
    transform: translateY(0.0009px) scale(1, 1);
  }
  62.7119% {
    transform: translateY(-0.0044px) scale(1.0001, 1.0001);
  }
  64.4068% {
    transform: translateY(-0.0073px) scale(1.0001, 1.0001);
  }
  66.1017% {
    transform: translateY(-0.0085px) scale(1.0002, 1.0002);
  }
  67.7966% {
    transform: translateY(-0.0084px) scale(1.0002, 1.0002);
  }
  69.4915% {
    transform: translateY(-0.0077px) scale(1.0001, 1.0001);
  }
  71.1864% {
    transform: translateY(-0.0065px) scale(1.0001, 1.0001);
  }
  72.8814% {
    transform: translateY(-0.0052px) scale(1.0001, 1.0001);
  }
  74.5763% {
    transform: translateY(-0.0039px) scale(1.0001, 1.0001);
  }
  76.2712% {
    transform: translateY(-0.0027px) scale(1.0001, 1.0001);
  }
  77.9661% {
    transform: translateY(-0.0018px) scale(1, 1);
  }
  79.6610% {
    transform: translateY(-0.001px) scale(1, 1);
  }
  81.3559% {
    transform: translateY(-0.0004px) scale(1, 1);
  }
  83.0508% {
    transform: translateY(-0.0001px) scale(1, 1);
  }
  84.7458% {
    transform: translateY(0.0002px) scale(1, 1);
  }
  86.4407% {
    transform: translateY(0.0003px) scale(1, 1);
  }
  88.1356% {
    transform: translateY(0.0004px) scale(1, 1);
  }
  89.8305% {
    transform: translateY(0.0004px) scale(1, 1);
  }
  91.5254% {
    transform: translateY(0.0003px) scale(1, 1);
  }
  93.2203% {
    transform: translateY(0.0003px) scale(1, 1);
  }
  94.9153% {
    transform: translateY(0.0002px) scale(1, 1);
  }
  96.6102% {
    transform: translateY(0.0002px) scale(1, 1);
  }
  98.3051% {
    transform: translateY(0.0001px) scale(1, 1);
  }
  100% {
    opacity: 1;
    transform: translateY(0.0001px) scale(1, 1);
  }
}
@-webkit-keyframes head-3-anim {
  0% {
    opacity: 0.0178;
    transform: translateY(53.0397px) scale(0.0178, 0.0178);
  }
  1.5385% {
    opacity: 0.046;
    transform: translateY(51.5168px) scale(0.046, 0.046);
  }
  3.0769% {
    opacity: 0.092;
    transform: translateY(49.0301px) scale(0.092, 0.092);
  }
  4.6154% {
    opacity: 0.1569;
    transform: translateY(45.5294px) scale(0.1569, 0.1569);
  }
  6.1538% {
    opacity: 0.2389;
    transform: translateY(41.0973px) scale(0.2389, 0.2389);
  }
  7.6923% {
    opacity: 0.3347;
    transform: translateY(35.9277px) scale(0.3347, 0.3347);
  }
  9.2308% {
    opacity: 0.4391;
    transform: translateY(30.2897px) scale(0.4391, 0.4391);
  }
  10.7692% {
    opacity: 0.5466;
    transform: translateY(24.4858px) scale(0.5466, 0.5466);
  }
  12.3077% {
    opacity: 0.6516;
    transform: translateY(18.8122px) scale(0.6516, 0.6516);
  }
  13.8462% {
    opacity: 0.7495;
    transform: translateY(13.5277px) scale(0.7495, 0.7495);
  }
  15.3846% {
    opacity: 0.8364;
    transform: translateY(8.8324px) scale(0.8364, 0.8364);
  }
  16.9231% {
    opacity: 0.91;
    transform: translateY(4.8579px) scale(0.91, 0.91);
  }
  18.4615% {
    opacity: 0.9691;
    transform: translateY(1.6664px) scale(0.9691, 0.9691);
  }
  20% {
    opacity: 1;
    transform: translateY(-0.7419px) scale(1.0137, 1.0137);
  }
  21.5385% {
    transform: translateY(-2.4176px) scale(1.0448, 1.0448);
  }
  23.0769% {
    transform: translateY(-3.4473px) scale(1.0638, 1.0638);
  }
  24.6154% {
    transform: translateY(-3.9398px) scale(1.073, 1.073);
  }
  26.1538% {
    transform: translateY(-4.0124px) scale(1.0743, 1.0743);
  }
  27.6923% {
    transform: translateY(-3.7806px) scale(1.07, 1.07);
  }
  29.2308% {
    transform: translateY(-3.3496px) scale(1.062, 1.062);
  }
  30.7692% {
    transform: translateY(-2.8095px) scale(1.052, 1.052);
  }
  32.3077% {
    transform: translateY(-2.2324px) scale(1.0413, 1.0413);
  }
  33.8462% {
    transform: translateY(-1.6721px) scale(1.031, 1.031);
  }
  35.3846% {
    transform: translateY(-1.1651px) scale(1.0216, 1.0216);
  }
  36.9231% {
    transform: translateY(-0.733px) scale(1.0136, 1.0136);
  }
  38.4615% {
    transform: translateY(-0.3849px) scale(1.0071, 1.0071);
  }
  40% {
    opacity: 1;
    transform: translateY(-0.1208px) scale(1.0022, 1.0022);
  }
  41.5385% {
    opacity: 0.9988;
    transform: translateY(0.0659px) scale(0.9988, 0.9988);
  }
  43.0769% {
    opacity: 0.9966;
    transform: translateY(0.1857px) scale(0.9966, 0.9966);
  }
  44.6154% {
    opacity: 0.9954;
    transform: translateY(0.2511px) scale(0.9954, 0.9954);
  }
  46.1538% {
    opacity: 0.9949;
    transform: translateY(0.2747px) scale(0.9949, 0.9949);
  }
  47.6923% {
    opacity: 0.995;
    transform: translateY(0.2685px) scale(0.995, 0.995);
  }
  49.2308% {
    opacity: 0.9955;
    transform: translateY(0.2428px) scale(0.9955, 0.9955);
  }
  50.7692% {
    opacity: 0.9962;
    transform: translateY(0.2063px) scale(0.9962, 0.9962);
  }
  52.3077% {
    opacity: 0.9969;
    transform: translateY(0.1656px) scale(0.9969, 0.9969);
  }
  53.8462% {
    opacity: 0.9977;
    transform: translateY(0.1253px) scale(0.9977, 0.9977);
  }
  55.3846% {
    opacity: 0.9984;
    transform: translateY(0.0887px) scale(0.9984, 0.9984);
  }
  56.9231% {
    opacity: 0.9989;
    transform: translateY(0.0574px) scale(0.9989, 0.9989);
  }
  58.4615% {
    opacity: 0.9994;
    transform: translateY(0.0322px) scale(0.9994, 0.9994);
  }
  60% {
    opacity: 0.9998;
    transform: translateY(0.0131px) scale(0.9998, 0.9998);
  }
  61.5385% {
    opacity: 1;
    transform: translateY(-0.0004px) scale(1, 1);
  }
  63.0769% {
    transform: translateY(-0.0092px) scale(1.0002, 1.0002);
  }
  64.6154% {
    transform: translateY(-0.0141px) scale(1.0003, 1.0003);
  }
  66.1538% {
    transform: translateY(-0.0161px) scale(1.0003, 1.0003);
  }
  67.6923% {
    transform: translateY(-0.0161px) scale(1.0003, 1.0003);
  }
  69.2308% {
    transform: translateY(-0.0147px) scale(1.0003, 1.0003);
  }
  70.7692% {
    transform: translateY(-0.0125px) scale(1.0002, 1.0002);
  }
  72.3077% {
    transform: translateY(-0.0101px) scale(1.0002, 1.0002);
  }
  73.8462% {
    transform: translateY(-0.0077px) scale(1.0001, 1.0001);
  }
  75.3846% {
    transform: translateY(-0.0054px) scale(1.0001, 1.0001);
  }
  76.9231% {
    transform: translateY(-0.0036px) scale(1.0001, 1.0001);
  }
  78.4615% {
    transform: translateY(-0.002px) scale(1, 1);
  }
  80% {
    transform: translateY(-0.0009px) scale(1, 1);
  }
  81.5385% {
    transform: translateY(-0.0001px) scale(1, 1);
  }
  83.0769% {
    transform: translateY(0.0004px) scale(1, 1);
  }
  84.6154% {
    transform: translateY(0.0007px) scale(1, 1);
  }
  86.1538% {
    transform: translateY(0.0009px) scale(1, 1);
  }
  87.6923% {
    transform: translateY(0.0009px) scale(1, 1);
  }
  89.2308% {
    transform: translateY(0.0008px) scale(1, 1);
  }
  90.7692% {
    transform: translateY(0.0007px) scale(1, 1);
  }
  92.3077% {
    transform: translateY(0.0005px) scale(1, 1);
  }
  93.8462% {
    transform: translateY(0.0004px) scale(1, 1);
  }
  95.3846% {
    transform: translateY(0.0003px) scale(1, 1);
  }
  96.9231% {
    transform: translateY(0.0002px) scale(1, 1);
  }
  98.4615% {
    transform: translateY(0.0001px) scale(1, 1);
  }
  100% {
    opacity: 1;
    transform: translateY(0.0001px) scale(1, 1);
  }
}
@-webkit-keyframes head-4-anim {
  0% {
    opacity: 0.0036;
    transform: translateY(53.8066px) scale(0.0036, 0.0036);
  }
  1.7857% {
    opacity: 0.0112;
    transform: translateY(53.3939px) scale(0.0112, 0.0112);
  }
  3.5714% {
    opacity: 0.0265;
    transform: translateY(52.5668px) scale(0.0265, 0.0265);
  }
  5.3571% {
    opacity: 0.0524;
    transform: translateY(51.1686px) scale(0.0524, 0.0524);
  }
  7.1429% {
    opacity: 0.0912;
    transform: translateY(49.076px) scale(0.0912, 0.0912);
  }
  8.9286% {
    opacity: 0.144;
    transform: translateY(46.2232px) scale(0.144, 0.144);
  }
  10.7143% {
    opacity: 0.2108;
    transform: translateY(42.6163px) scale(0.2108, 0.2108);
  }
  12.5000% {
    opacity: 0.2901;
    transform: translateY(38.3347px) scale(0.2901, 0.2901);
  }
  14.2857% {
    opacity: 0.3792;
    transform: translateY(33.5229px) scale(0.3792, 0.3792);
  }
  16.0714% {
    opacity: 0.4746;
    transform: translateY(28.3723px) scale(0.4746, 0.4746);
  }
  17.8571% {
    opacity: 0.5722;
    transform: translateY(23.1004px) scale(0.5722, 0.5722);
  }
  19.6429% {
    opacity: 0.668;
    transform: translateY(17.9267px) scale(0.668, 0.668);
  }
  21.4286% {
    opacity: 0.7583;
    transform: translateY(13.0531px) scale(0.7583, 0.7583);
  }
  23.2143% {
    opacity: 0.8399;
    transform: translateY(8.6473px) scale(0.8399, 0.8399);
  }
  25.0000% {
    opacity: 0.9105;
    transform: translateY(4.8318px) scale(0.9105, 0.9105);
  }
  26.7857% {
    opacity: 0.9689;
    transform: translateY(1.6802px) scale(0.9689, 0.9689);
  }
  28.5714% {
    opacity: 1;
    transform: translateY(-0.7827px) scale(1.0145, 1.0145);
  }
  30.3571% {
    transform: translateY(-2.5753px) scale(1.0477, 1.0477);
  }
  32.1429% {
    transform: translateY(-3.7516px) scale(1.0695, 1.0695);
  }
  33.9286% {
    transform: translateY(-4.3905px) scale(1.0813, 1.0813);
  }
  35.7143% {
    transform: translateY(-4.5866px) scale(1.0849, 1.0849);
  }
  37.5000% {
    transform: translateY(-4.4404px) scale(1.0822, 1.0822);
  }
  39.2857% {
    transform: translateY(-4.05px) scale(1.075, 1.075);
  }
  41.0714% {
    transform: translateY(-3.5055px) scale(1.0649, 1.0649);
  }
  42.8571% {
    transform: translateY(-2.8841px) scale(1.0534, 1.0534);
  }
  44.6429% {
    transform: translateY(-2.2483px) scale(1.0416, 1.0416);
  }
  46.4286% {
    transform: translateY(-1.6452px) scale(1.0305, 1.0305);
  }
  48.2143% {
    transform: translateY(-1.1067px) scale(1.0205, 1.0205);
  }
  50% {
    transform: translateY(-0.6515px) scale(1.0121, 1.0121);
  }
  51.7857% {
    transform: translateY(-0.2875px) scale(1.0053, 1.0053);
  }
  53.5714% {
    opacity: 1;
    transform: translateY(-0.0135px) scale(1.0002, 1.0002);
  }
  55.3571% {
    opacity: 0.9967;
    transform: translateY(0.1776px) scale(0.9967, 0.9967);
  }
  57.1429% {
    opacity: 0.9945;
    transform: translateY(0.2968px) scale(0.9945, 0.9945);
  }
  58.9286% {
    opacity: 0.9934;
    transform: translateY(0.3572px) scale(0.9934, 0.9934);
  }
  60.7143% {
    opacity: 0.9931;
    transform: translateY(0.3724px) scale(0.9931, 0.9931);
  }
  62.5000% {
    opacity: 0.9934;
    transform: translateY(0.3551px) scale(0.9934, 0.9934);
  }
  64.2857% {
    opacity: 0.9941;
    transform: translateY(0.3167px) scale(0.9941, 0.9941);
  }
  66.0714% {
    opacity: 0.9951;
    transform: translateY(0.2666px) scale(0.9951, 0.9951);
  }
  67.8571% {
    opacity: 0.9961;
    transform: translateY(0.2124px) scale(0.9961, 0.9961);
  }
  69.6429% {
    opacity: 0.997;
    transform: translateY(0.1595px) scale(0.997, 0.997);
  }
  71.4286% {
    opacity: 0.9979;
    transform: translateY(0.1115px) scale(0.9979, 0.9979);
  }
  73.2143% {
    opacity: 0.9987;
    transform: translateY(0.0705px) scale(0.9987, 0.9987);
  }
  75.0000% {
    opacity: 0.9993;
    transform: translateY(0.0375px) scale(0.9993, 0.9993);
  }
  76.7857% {
    opacity: 0.9998;
    transform: translateY(0.0124px) scale(0.9998, 0.9998);
  }
  78.5714% {
    opacity: 1;
    transform: translateY(-0.0054px) scale(1.0001, 1.0001);
  }
  80.3571% {
    transform: translateY(-0.0169px) scale(1.0003, 1.0003);
  }
  82.1429% {
    transform: translateY(-0.0232px) scale(1.0004, 1.0004);
  }
  83.9286% {
    transform: translateY(-0.0256px) scale(1.0005, 1.0005);
  }
  85.7143% {
    transform: translateY(-0.0251px) scale(1.0005, 1.0005);
  }
  87.5000% {
    transform: translateY(-0.0228px) scale(1.0004, 1.0004);
  }
  89.2857% {
    transform: translateY(-0.0194px) scale(1.0004, 1.0004);
  }
  91.0714% {
    transform: translateY(-0.0156px) scale(1.0003, 1.0003);
  }
  92.8571% {
    transform: translateY(-0.0119px) scale(1.0002, 1.0002);
  }
  94.6429% {
    transform: translateY(-0.0084px) scale(1.0002, 1.0002);
  }
  96.4286% {
    transform: translateY(-0.0055px) scale(1.0001, 1.0001);
  }
  98.2143% {
    transform: translateY(-0.0031px) scale(1.0001, 1.0001);
  }
  100% {
    opacity: 1;
    transform: translateY(-0.0013px) scale(1, 1);
  }
}
@-webkit-keyframes head-5-anim {
  0% {
    opacity: 0.0007;
    transform: translateY(53.9627px) scale(0.0007, 0.0007);
  }
  1.3699% {
    opacity: 0.0025;
    transform: translateY(53.8625px) scale(0.0025, 0.0025);
  }
  2.7397% {
    opacity: 0.007;
    transform: translateY(53.6246px) scale(0.007, 0.007);
  }
  4.1096% {
    opacity: 0.0156;
    transform: translateY(53.1558px) scale(0.0156, 0.0156);
  }
  5.4795% {
    opacity: 0.0306;
    transform: translateY(52.3476px) scale(0.0306, 0.0306);
  }
  6.8493% {
    opacity: 0.0539;
    transform: translateY(51.0902px) scale(0.0539, 0.0539);
  }
  8.2192% {
    opacity: 0.0872;
    transform: translateY(49.2888px) scale(0.0872, 0.0872);
  }
  9.5890% {
    opacity: 0.1319;
    transform: translateY(46.8789px) scale(0.1319, 0.1319);
  }
  10.9589% {
    opacity: 0.1882;
    transform: translateY(43.8388px) scale(0.1882, 0.1882);
  }
  12.3288% {
    opacity: 0.2556;
    transform: translateY(40.1957px) scale(0.2556, 0.2556);
  }
  13.6986% {
    opacity: 0.3328;
    transform: translateY(36.0263px) scale(0.3328, 0.3328);
  }
  15.0685% {
    opacity: 0.4176;
    transform: translateY(31.4508px) scale(0.4176, 0.4176);
  }
  16.4384% {
    opacity: 0.507;
    transform: translateY(26.6223px) scale(0.507, 0.507);
  }
  17.8082% {
    opacity: 0.5979;
    transform: translateY(21.7127px) scale(0.5979, 0.5979);
  }
  19.1781% {
    opacity: 0.6871;
    transform: translateY(16.8978px) scale(0.6871, 0.6871);
  }
  20.5479% {
    opacity: 0.7714;
    transform: translateY(12.3424px) scale(0.7714, 0.7714);
  }
  21.9178% {
    opacity: 0.8484;
    transform: translateY(8.1883px) scale(0.8484, 0.8484);
  }
  23.2877% {
    opacity: 0.9158;
    transform: translateY(4.5454px) scale(0.9158, 0.9158);
  }
  24.6575% {
    opacity: 0.9725;
    transform: translateY(1.4862px) scale(0.9725, 0.9725);
  }
  26.0274% {
    opacity: 1;
    transform: translateY(-0.9554px) scale(1.0177, 1.0177);
  }
  27.3973% {
    transform: translateY(-2.7819px) scale(1.0515, 1.0515);
  }
  28.7671% {
    transform: translateY(-4.0276px) scale(1.0746, 1.0746);
  }
  30.1370% {
    transform: translateY(-4.7517px) scale(1.088, 1.088);
  }
  31.5068% {
    transform: translateY(-5.0309px) scale(1.0932, 1.0932);
  }
  32.8767% {
    transform: translateY(-4.9516px) scale(1.0917, 1.0917);
  }
  34.2466% {
    transform: translateY(-4.6028px) scale(1.0852, 1.0852);
  }
  35.6164% {
    transform: translateY(-4.07px) scale(1.0754, 1.0754);
  }
  36.9863% {
    transform: translateY(-3.4305px) scale(1.0635, 1.0635);
  }
  38.3562% {
    transform: translateY(-2.75px) scale(1.0509, 1.0509);
  }
  39.7260% {
    transform: translateY(-2.0814px) scale(1.0385, 1.0385);
  }
  41.0959% {
    transform: translateY(-1.4636px) scale(1.0271, 1.0271);
  }
  42.4658% {
    transform: translateY(-0.9228px) scale(1.0171, 1.0171);
  }
  43.8356% {
    transform: translateY(-0.4734px) scale(1.0088, 1.0088);
  }
  45.2055% {
    opacity: 1;
    transform: translateY(-0.1199px) scale(1.0022, 1.0022);
  }
  46.5753% {
    opacity: 0.9974;
    transform: translateY(0.1404px) scale(0.9974, 0.9974);
  }
  47.9452% {
    opacity: 0.9941;
    transform: translateY(0.3161px) scale(0.9941, 0.9941);
  }
  49.3151% {
    opacity: 0.9922;
    transform: translateY(0.419px) scale(0.9922, 0.9922);
  }
  50.6849% {
    opacity: 0.9914;
    transform: translateY(0.4628px) scale(0.9914, 0.9914);
  }
  52.0548% {
    opacity: 0.9915;
    transform: translateY(0.4615px) scale(0.9915, 0.9915);
  }
  53.4247% {
    opacity: 0.9921;
    transform: translateY(0.4282px) scale(0.9921, 0.9921);
  }
  54.7945% {
    opacity: 0.9931;
    transform: translateY(0.3747px) scale(0.9931, 0.9931);
  }
  56.1644% {
    opacity: 0.9942;
    transform: translateY(0.3106px) scale(0.9942, 0.9942);
  }
  57.5342% {
    opacity: 0.9955;
    transform: translateY(0.2438px) scale(0.9955, 0.9955);
  }
  58.9041% {
    opacity: 0.9967;
    transform: translateY(0.1798px) scale(0.9967, 0.9967);
  }
  60.2740% {
    opacity: 0.9977;
    transform: translateY(0.1224px) scale(0.9977, 0.9977);
  }
  61.6438% {
    opacity: 0.9986;
    transform: translateY(0.0739px) scale(0.9986, 0.9986);
  }
  63.0137% {
    opacity: 0.9994;
    transform: translateY(0.035px) scale(0.9994, 0.9994);
  }
  64.3836% {
    opacity: 0.9999;
    transform: translateY(0.0057px) scale(0.9999, 0.9999);
  }
  65.7534% {
    opacity: 1;
    transform: translateY(-0.0148px) scale(1.0003, 1.0003);
  }
  67.1233% {
    transform: translateY(-0.0277px) scale(1.0005, 1.0005);
  }
  68.4932% {
    transform: translateY(-0.0345px) scale(1.0006, 1.0006);
  }
  69.8630% {
    transform: translateY(-0.0364px) scale(1.0007, 1.0007);
  }
  71.2329% {
    transform: translateY(-0.035px) scale(1.0006, 1.0006);
  }
  72.6027% {
    transform: translateY(-0.0314px) scale(1.0006, 1.0006);
  }
  73.9726% {
    transform: translateY(-0.0265px) scale(1.0005, 1.0005);
  }
  75.3425% {
    transform: translateY(-0.0212px) scale(1.0004, 1.0004);
  }
  76.7123% {
    transform: translateY(-0.016px) scale(1.0003, 1.0003);
  }
  78.0822% {
    transform: translateY(-0.0112px) scale(1.0002, 1.0002);
  }
  79.4521% {
    transform: translateY(-0.0071px) scale(1.0001, 1.0001);
  }
  80.8219% {
    transform: translateY(-0.0038px) scale(1.0001, 1.0001);
  }
  82.1918% {
    transform: translateY(-0.0013px) scale(1, 1);
  }
  83.5616% {
    transform: translateY(0.0005px) scale(1, 1);
  }
  84.9315% {
    transform: translateY(0.0016px) scale(1, 1);
  }
  86.3014% {
    transform: translateY(0.0023px) scale(1, 1);
  }
  87.6712% {
    transform: translateY(0.0025px) scale(1, 1);
  }
  89.0411% {
    transform: translateY(0.0025px) scale(1, 1);
  }
  90.4110% {
    transform: translateY(0.0023px) scale(1, 1);
  }
  91.7808% {
    transform: translateY(0.0019px) scale(1, 1);
  }
  93.1507% {
    transform: translateY(0.0016px) scale(1, 1);
  }
  94.5205% {
    transform: translateY(0.0012px) scale(1, 1);
  }
  95.8904% {
    transform: translateY(0.0008px) scale(1, 1);
  }
  97.2603% {
    transform: translateY(0.0005px) scale(1, 1);
  }
  98.6301% {
    transform: translateY(0.0003px) scale(1, 1);
  }
  100% {
    opacity: 1;
    transform: translateY(0.0001px) scale(1, 1);
  }
}
@-webkit-keyframes head-6-anim {
  0% {
    opacity: 0.0001;
    transform: translateY(53.993px) scale(0.0001, 0.0001);
  }
  1.2987% {
    opacity: 0.0005;
    transform: translateY(53.9704px) scale(0.0005, 0.0005);
  }
  2.5974% {
    opacity: 0.0017;
    transform: translateY(53.9083px) scale(0.0017, 0.0017);
  }
  3.8961% {
    opacity: 0.0043;
    transform: translateY(53.7685px) scale(0.0043, 0.0043);
  }
  5.1948% {
    opacity: 0.0093;
    transform: translateY(53.496px) scale(0.0093, 0.0093);
  }
  6.4935% {
    opacity: 0.0181;
    transform: translateY(53.0204px) scale(0.0181, 0.0181);
  }
  7.7922% {
    opacity: 0.0322;
    transform: translateY(52.2602px) scale(0.0322, 0.0322);
  }
  9.0909% {
    opacity: 0.0531;
    transform: translateY(51.1316px) scale(0.0531, 0.0531);
  }
  10.3896% {
    opacity: 0.0823;
    transform: translateY(49.5575px) scale(0.0823, 0.0823);
  }
  11.6883% {
    opacity: 0.1208;
    transform: translateY(47.4782px) scale(0.1208, 0.1208);
  }
  12.9870% {
    opacity: 0.1692;
    transform: translateY(44.861px) scale(0.1692, 0.1692);
  }
  14.2857% {
    opacity: 0.2277;
    transform: translateY(41.7064px) scale(0.2277, 0.2277);
  }
  15.5844% {
    opacity: 0.2953;
    transform: translateY(38.0522px) scale(0.2953, 0.2953);
  }
  16.8831% {
    opacity: 0.3709;
    transform: translateY(33.9721px) scale(0.3709, 0.3709);
  }
  18.1818% {
    opacity: 0.4524;
    transform: translateY(29.572px) scale(0.4524, 0.4524);
  }
  19.4805% {
    opacity: 0.5374;
    transform: translateY(24.9815px) scale(0.5374, 0.5374);
  }
  20.7792% {
    opacity: 0.6232;
    transform: translateY(20.3447px) scale(0.6232, 0.6232);
  }
  22.0779% {
    opacity: 0.7072;
    transform: translateY(15.8086px) scale(0.7072, 0.7072);
  }
  23.3766% {
    opacity: 0.7868;
    transform: translateY(11.5126px) scale(0.7868, 0.7868);
  }
  24.6753% {
    opacity: 0.8597;
    transform: translateY(7.5788px) scale(0.8597, 0.8597);
  }
  25.9740% {
    opacity: 0.924;
    transform: translateY(4.1046px) scale(0.924, 0.924);
  }
  27.2727% {
    opacity: 0.9786;
    transform: translateY(1.158px) scale(0.9786, 0.9786);
  }
  28.5714% {
    opacity: 1;
    transform: translateY(-1.2247px) scale(1.0227, 1.0227);
  }
  29.8701% {
    transform: translateY(-3.0381px) scale(1.0563, 1.0563);
  }
  31.1688% {
    transform: translateY(-4.3048px) scale(1.0797, 1.0797);
  }
  32.4675% {
    transform: translateY(-5.0707px) scale(1.0939, 1.0939);
  }
  33.7662% {
    transform: translateY(-5.3993px) scale(1.1, 1.1);
  }
  35.0649% {
    transform: translateY(-5.3657px) scale(1.0994, 1.0994);
  }
  36.3636% {
    transform: translateY(-5.0499px) scale(1.0935, 1.0935);
  }
  37.6623% {
    transform: translateY(-4.5316px) scale(1.0839, 1.0839);
  }
  38.9610% {
    transform: translateY(-3.8857px) scale(1.072, 1.072);
  }
  40.2597% {
    transform: translateY(-3.1781px) scale(1.0589, 1.0589);
  }
  41.5584% {
    transform: translateY(-2.4645px) scale(1.0456, 1.0456);
  }
  42.8571% {
    transform: translateY(-1.7879px) scale(1.0331, 1.0331);
  }
  44.1558% {
    transform: translateY(-1.1798px) scale(1.0218, 1.0218);
  }
  45.4545% {
    transform: translateY(-0.6597px) scale(1.0122, 1.0122);
  }
  46.7532% {
    opacity: 1;
    transform: translateY(-0.2373px) scale(1.0044, 1.0044);
  }
  48.0519% {
    opacity: 0.9984;
    transform: translateY(0.0862px) scale(0.9984, 0.9984);
  }
  49.3506% {
    opacity: 0.9941;
    transform: translateY(0.3163px) scale(0.9941, 0.9941);
  }
  50.6494% {
    opacity: 0.9914;
    transform: translateY(0.4629px) scale(0.9914, 0.9914);
  }
  51.9481% {
    opacity: 0.99;
    transform: translateY(0.5389px) scale(0.99, 0.99);
  }
  53.2468% {
    opacity: 0.9897;
    transform: translateY(0.5583px) scale(0.9897, 0.9897);
  }
  54.5455% {
    opacity: 0.9901;
    transform: translateY(0.5354px) scale(0.9901, 0.9901);
  }
  55.8442% {
    opacity: 0.9911;
    transform: translateY(0.4833px) scale(0.9911, 0.9911);
  }
  57.1429% {
    opacity: 0.9923;
    transform: translateY(0.4136px) scale(0.9923, 0.9923);
  }
  58.4416% {
    opacity: 0.9938;
    transform: translateY(0.3359px) scale(0.9938, 0.9938);
  }
  59.7403% {
    opacity: 0.9952;
    transform: translateY(0.2579px) scale(0.9952, 0.9952);
  }
  61.0390% {
    opacity: 0.9966;
    transform: translateY(0.1849px) scale(0.9966, 0.9966);
  }
  62.3377% {
    opacity: 0.9978;
    transform: translateY(0.1206px) scale(0.9978, 0.9978);
  }
  63.6364% {
    opacity: 0.9988;
    transform: translateY(0.0669px) scale(0.9988, 0.9988);
  }
  64.9351% {
    opacity: 0.9995;
    transform: translateY(0.0245px) scale(0.9995, 0.9995);
  }
  66.2338% {
    opacity: 1;
    transform: translateY(-0.0069px) scale(1.0001, 1.0001);
  }
  67.5325% {
    transform: translateY(-0.0284px) scale(1.0005, 1.0005);
  }
  68.8312% {
    transform: translateY(-0.0413px) scale(1.0008, 1.0008);
  }
  70.1299% {
    transform: translateY(-0.0473px) scale(1.0009, 1.0009);
  }
  71.4286% {
    transform: translateY(-0.0481px) scale(1.0009, 1.0009);
  }
  72.7273% {
    transform: translateY(-0.0451px) scale(1.0008, 1.0008);
  }
  74.0260% {
    transform: translateY(-0.0397px) scale(1.0007, 1.0007);
  }
  75.3247% {
    transform: translateY(-0.0331px) scale(1.0006, 1.0006);
  }
  76.6234% {
    transform: translateY(-0.0261px) scale(1.0005, 1.0005);
  }
  77.9221% {
    transform: translateY(-0.0194px) scale(1.0004, 1.0004);
  }
  79.2208% {
    transform: translateY(-0.0133px) scale(1.0002, 1.0002);
  }
  80.5195% {
    transform: translateY(-0.0081px) scale(1.0002, 1.0002);
  }
  81.8182% {
    transform: translateY(-0.004px) scale(1.0001, 1.0001);
  }
  83.1169% {
    transform: translateY(-0.0009px) scale(1, 1);
  }
  84.4156% {
    transform: translateY(0.0013px) scale(1, 1);
  }
  85.7143% {
    opacity: 1;
    transform: translateY(0.0027px) scale(1, 1);
  }
  87.0130% {
    opacity: 0.9999;
    transform: translateY(0.0034px) scale(0.9999, 0.9999);
  }
  88.3117% {
    transform: translateY(0.0037px) scale(0.9999, 0.9999);
  }
  89.6104% {
    transform: translateY(0.0036px) scale(0.9999, 0.9999);
  }
  90.9091% {
    transform: translateY(0.0032px) scale(0.9999, 0.9999);
  }
  92.2078% {
    opacity: 0.9999;
    transform: translateY(0.0027px) scale(0.9999, 0.9999);
  }
  93.5065% {
    opacity: 1;
    transform: translateY(0.0022px) scale(1, 1);
  }
  94.8052% {
    transform: translateY(0.0016px) scale(1, 1);
  }
  96.1039% {
    transform: translateY(0.0012px) scale(1, 1);
  }
  97.4026% {
    transform: translateY(0.0007px) scale(1, 1);
  }
  98.7013% {
    transform: translateY(0.0004px) scale(1, 1);
  }
  100% {
    opacity: 1;
    transform: translateY(0.0001px) scale(1, 1);
  }
}
@-webkit-keyframes head-7-anim {
  0% {
    opacity: 0;
    transform: translateY(53.9987px) scale(0, 0);
  }
  1.0870% {
    opacity: 0.0001;
    transform: translateY(53.9939px) scale(0.0001, 0.0001);
  }
  2.1739% {
    opacity: 0.0004;
    transform: translateY(53.9787px) scale(0.0004, 0.0004);
  }
  3.2609% {
    opacity: 0.0011;
    transform: translateY(53.9404px) scale(0.0011, 0.0011);
  }
  4.3478% {
    opacity: 0.0026;
    transform: translateY(53.8572px) scale(0.0026, 0.0026);
  }
  5.4348% {
    opacity: 0.0056;
    transform: translateY(53.6962px) scale(0.0056, 0.0056);
  }
  6.5217% {
    opacity: 0.0109;
    transform: translateY(53.4127px) scale(0.0109, 0.0109);
  }
  7.6087% {
    opacity: 0.0194;
    transform: translateY(52.9506px) scale(0.0194, 0.0194);
  }
  8.6957% {
    opacity: 0.0325;
    transform: translateY(52.2458px) scale(0.0325, 0.0325);
  }
  9.7826% {
    opacity: 0.0513;
    transform: translateY(51.2306px) scale(0.0513, 0.0513);
  }
  10.8696% {
    opacity: 0.077;
    transform: translateY(49.8406px) scale(0.077, 0.077);
  }
  11.9565% {
    opacity: 0.1107;
    transform: translateY(48.0213px) scale(0.1107, 0.1107);
  }
  13.0435% {
    opacity: 0.153;
    transform: translateY(45.7358px) scale(0.153, 0.153);
  }
  14.1304% {
    opacity: 0.2042;
    transform: translateY(42.9705px) scale(0.2042, 0.2042);
  }
  15.2174% {
    opacity: 0.2641;
    transform: translateY(39.7396px) scale(0.2641, 0.2641);
  }
  16.3043% {
    opacity: 0.3317;
    transform: translateY(36.0862px) scale(0.3317, 0.3317);
  }
  17.3913% {
    opacity: 0.4059;
    transform: translateY(32.0817px) scale(0.4059, 0.4059);
  }
  18.4783% {
    opacity: 0.4848;
    transform: translateY(27.8219px) scale(0.4848, 0.4848);
  }
  19.5652% {
    opacity: 0.5663;
    transform: translateY(23.421px) scale(0.5663, 0.5663);
  }
  20.6522% {
    opacity: 0.6481;
    transform: translateY(19.0036px) scale(0.6481, 0.6481);
  }
  21.7391% {
    opacity: 0.7278;
    transform: translateY(14.6966px) scale(0.7278, 0.7278);
  }
  22.8261% {
    opacity: 0.8033;
    transform: translateY(10.6207px) scale(0.8033, 0.8033);
  }
  23.9130% {
    opacity: 0.8725;
    transform: translateY(6.8826px) scale(0.8725, 0.8725);
  }
  25.0000% {
    opacity: 0.9339;
    transform: translateY(3.5691px) scale(0.9339, 0.9339);
  }
  26.0870% {
    opacity: 0.9863;
    transform: translateY(0.7423px) scale(0.9863, 0.9863);
  }
  27.1739% {
    opacity: 1;
    transform: translateY(-1.5619px) scale(1.0289, 1.0289);
  }
  28.2609% {
    transform: translateY(-3.3344px) scale(1.0617, 1.0617);
  }
  29.3478% {
    transform: translateY(-4.5908px) scale(1.085, 1.085);
  }
  30.4348% {
    transform: translateY(-5.3682px) scale(1.0994, 1.0994);
  }
  31.5217% {
    transform: translateY(-5.7205px) scale(1.1059, 1.1059);
  }
  32.6087% {
    transform: translateY(-5.7136px) scale(1.1058, 1.1058);
  }
  33.6957% {
    transform: translateY(-5.4198px) scale(1.1004, 1.1004);
  }
  34.7826% {
    transform: translateY(-4.9131px) scale(1.091, 1.091);
  }
  35.8696% {
    transform: translateY(-4.2648px) scale(1.079, 1.079);
  }
  36.9565% {
    transform: translateY(-3.5398px) scale(1.0656, 1.0656);
  }
  38.0435% {
    transform: translateY(-2.7942px) scale(1.0517, 1.0517);
  }
  39.1304% {
    transform: translateY(-2.0737px) scale(1.0384, 1.0384);
  }
  40.2174% {
    transform: translateY(-1.4128px) scale(1.0262, 1.0262);
  }
  41.3043% {
    transform: translateY(-0.8351px) scale(1.0155, 1.0155);
  }
  42.3913% {
    opacity: 1;
    transform: translateY(-0.3543px) scale(1.0066, 1.0066);
  }
  43.4783% {
    opacity: 0.9995;
    transform: translateY(0.025px) scale(0.9995, 0.9995);
  }
  44.5652% {
    opacity: 0.9944;
    transform: translateY(0.305px) scale(0.9944, 0.9944);
  }
  45.6522% {
    opacity: 0.9909;
    transform: translateY(0.4937px) scale(0.9909, 0.9909);
  }
  46.7391% {
    opacity: 0.9888;
    transform: translateY(0.6025px) scale(0.9888, 0.9888);
  }
  47.8261% {
    opacity: 0.9881;
    transform: translateY(0.645px) scale(0.9881, 0.9881);
  }
  48.9130% {
    opacity: 0.9882;
    transform: translateY(0.6358px) scale(0.9882, 0.9882);
  }
  50% {
    opacity: 0.9891;
    transform: translateY(0.5888px) scale(0.9891, 0.9891);
  }
  51.0870% {
    opacity: 0.9904;
    transform: translateY(0.5171px) scale(0.9904, 0.9904);
  }
  52.1739% {
    opacity: 0.992;
    transform: translateY(0.4317px) scale(0.992, 0.992);
  }
  53.2609% {
    opacity: 0.9937;
    transform: translateY(0.3419px) scale(0.9937, 0.9937);
  }
  54.3478% {
    opacity: 0.9953;
    transform: translateY(0.2548px) scale(0.9953, 0.9953);
  }
  55.4348% {
    opacity: 0.9968;
    transform: translateY(0.1753px) scale(0.9968, 0.9968);
  }
  56.5217% {
    opacity: 0.998;
    transform: translateY(0.1066px) scale(0.998, 0.998);
  }
  57.6087% {
    opacity: 0.9991;
    transform: translateY(0.0504px) scale(0.9991, 0.9991);
  }
  58.6957% {
    opacity: 0.9999;
    transform: translateY(0.0068px) scale(0.9999, 0.9999);
  }
  59.7826% {
    opacity: 1;
    transform: translateY(-0.0246px) scale(1.0005, 1.0005);
  }
  60.8696% {
    transform: translateY(-0.0452px) scale(1.0008, 1.0008);
  }
  61.9565% {
    transform: translateY(-0.0567px) scale(1.0011, 1.0011);
  }
  63.0435% {
    transform: translateY(-0.0609px) scale(1.0011, 1.0011);
  }
  64.1304% {
    transform: translateY(-0.0596px) scale(1.0011, 1.0011);
  }
  65.2174% {
    transform: translateY(-0.0545px) scale(1.001, 1.001);
  }
  66.3043% {
    transform: translateY(-0.0471px) scale(1.0009, 1.0009);
  }
  67.3913% {
    transform: translateY(-0.0386px) scale(1.0007, 1.0007);
  }
  68.4783% {
    transform: translateY(-0.0299px) scale(1.0006, 1.0006);
  }
  69.5652% {
    transform: translateY(-0.0217px) scale(1.0004, 1.0004);
  }
  70.6522% {
    transform: translateY(-0.0144px) scale(1.0003, 1.0003);
  }
  71.7391% {
    transform: translateY(-0.0083px) scale(1.0002, 1.0002);
  }
  72.8261% {
    transform: translateY(-0.0034px) scale(1.0001, 1.0001);
  }
  73.9130% {
    transform: translateY(0.0002px) scale(1, 1);
  }
  75.0000% {
    opacity: 1;
    transform: translateY(0.0026px) scale(1, 1);
  }
  76.0870% {
    opacity: 0.9999;
    transform: translateY(0.0042px) scale(0.9999, 0.9999);
  }
  77.1739% {
    transform: translateY(0.0049px) scale(0.9999, 0.9999);
  }
  78.2609% {
    transform: translateY(0.005px) scale(0.9999, 0.9999);
  }
  79.3478% {
    transform: translateY(0.0048px) scale(0.9999, 0.9999);
  }
  80.4348% {
    transform: translateY(0.0042px) scale(0.9999, 0.9999);
  }
  81.5217% {
    transform: translateY(0.0035px) scale(0.9999, 0.9999);
  }
  82.6087% {
    opacity: 0.9999;
    transform: translateY(0.0028px) scale(0.9999, 0.9999);
  }
  83.6957% {
    opacity: 1;
    transform: translateY(0.0021px) scale(1, 1);
  }
  84.7826% {
    transform: translateY(0.0014px) scale(1, 1);
  }
  85.8696% {
    transform: translateY(0.0009px) scale(1, 1);
  }
  86.9565% {
    transform: translateY(0.0005px) scale(1, 1);
  }
  88.0435% {
    transform: translateY(0.0001px) scale(1, 1);
  }
  89.1304% {
    transform: translateY(-0.0001px) scale(1, 1);
  }
  90.2174% {
    transform: translateY(-0.0003px) scale(1, 1);
  }
  91.3043% {
    transform: translateY(-0.0003px) scale(1, 1);
  }
  92.3913% {
    transform: translateY(-0.0004px) scale(1, 1);
  }
  93.4783% {
    transform: translateY(-0.0004px) scale(1, 1);
  }
  94.5652% {
    transform: translateY(-0.0003px) scale(1, 1);
  }
  95.6522% {
    transform: translateY(-0.0003px) scale(1, 1);
  }
  96.7391% {
    transform: translateY(-0.0002px) scale(1, 1);
  }
  97.8261% {
    transform: translateY(-0.0002px) scale(1, 1);
  }
  98.9130% {
    transform: translateY(-0.0001px) scale(1, 1);
  }
  100% {
    opacity: 1;
    transform: translateY(-0.0001px) scale(1, 1);
  }
}
@-webkit-keyframes head-8-anim {
  0% {
    opacity: 0;
    transform: translateY(53.9998px) scale(0, 0);
  }
  1.1905% {
    opacity: 0;
    transform: translateY(53.9988px) scale(0, 0);
  }
  2.3810% {
    opacity: 0.0001;
    transform: translateY(53.9953px) scale(0.0001, 0.0001);
  }
  3.5714% {
    opacity: 0.0003;
    transform: translateY(53.9854px) scale(0.0003, 0.0003);
  }
  4.7619% {
    opacity: 0.0007;
    transform: translateY(53.9618px) scale(0.0007, 0.0007);
  }
  5.9524% {
    opacity: 0.0016;
    transform: translateY(53.9118px) scale(0.0016, 0.0016);
  }
  7.1429% {
    opacity: 0.0034;
    transform: translateY(53.8156px) scale(0.0034, 0.0034);
  }
  8.3333% {
    opacity: 0.0066;
    transform: translateY(53.6449px) scale(0.0066, 0.0066);
  }
  9.5238% {
    opacity: 0.0118;
    transform: translateY(53.3627px) scale(0.0118, 0.0118);
  }
  10.7143% {
    opacity: 0.0199;
    transform: translateY(52.923px) scale(0.0199, 0.0199);
  }
  11.9048% {
    opacity: 0.032;
    transform: translateY(52.2733px) scale(0.032, 0.032);
  }
  13.0952% {
    opacity: 0.0489;
    transform: translateY(51.3576px) scale(0.0489, 0.0489);
  }
  14.2857% {
    opacity: 0.0718;
    transform: translateY(50.1204px) scale(0.0718, 0.0718);
  }
  15.4762% {
    opacity: 0.1016;
    transform: translateY(48.5126px) scale(0.1016, 0.1016);
  }
  16.6667% {
    opacity: 0.139;
    transform: translateY(46.4962px) scale(0.139, 0.139);
  }
  17.8571% {
    opacity: 0.1843;
    transform: translateY(44.0501px) scale(0.1843, 0.1843);
  }
  19.0476% {
    opacity: 0.2375;
    transform: translateY(41.1737px) scale(0.2375, 0.2375);
  }
  20.2381% {
    opacity: 0.2983;
    transform: translateY(37.8896px) scale(0.2983, 0.2983);
  }
  21.4286% {
    opacity: 0.3658;
    transform: translateY(34.2443px) scale(0.3658, 0.3658);
  }
  22.6190% {
    opacity: 0.4388;
    transform: translateY(30.307px) scale(0.4388, 0.4388);
  }
  23.8095% {
    opacity: 0.5154;
    transform: translateY(26.166px) scale(0.5154, 0.5154);
  }
  25.0000% {
    opacity: 0.594;
    transform: translateY(21.924px) scale(0.594, 0.594);
  }
  26.1905% {
    opacity: 0.6724;
    transform: translateY(17.6916px) scale(0.6724, 0.6724);
  }
  27.3810% {
    opacity: 0.7485;
    transform: translateY(13.5807px) scale(0.7485, 0.7485);
  }
  28.5714% {
    opacity: 0.8204;
    transform: translateY(9.6975px) scale(0.8204, 0.8204);
  }
  29.7619% {
    opacity: 0.8864;
    transform: translateY(6.1365px) scale(0.8864, 0.8864);
  }
  30.9524% {
    opacity: 0.9449;
    transform: translateY(2.9751px) scale(0.9449, 0.9449);
  }
  32.1429% {
    opacity: 0.995;
    transform: translateY(0.2699px) scale(0.995, 0.995);
  }
  33.3333% {
    opacity: 1;
    transform: translateY(-1.9453px) scale(1.036, 1.036);
  }
  34.5238% {
    opacity: 1;
    transform: translateY(-3.6599px) scale(1.0678, 1.0678);
  }
  35.7143% {
    opacity: 1;
    transform: translateY(-4.8855px) scale(1.0905, 1.0905);
  }
  36.9048% {
    opacity: 1;
    transform: translateY(-5.653px) scale(1.1047, 1.1047);
  }
  38.0952% {
    opacity: 1;
    transform: translateY(-6.0095px) scale(1.1113, 1.1113);
  }
  39.2857% {
    opacity: 1;
    transform: translateY(-6.0136px) scale(1.1114, 1.1114);
  }
  40.4762% {
    opacity: 1;
    transform: translateY(-5.7312px) scale(1.1061, 1.1061);
  }
  41.6667% {
    opacity: 1;
    transform: translateY(-5.2311px) scale(1.0969, 1.0969);
  }
  42.8571% {
    opacity: 1;
    transform: translateY(-4.5808px) scale(1.0848, 1.0848);
  }
  44.0476% {
    opacity: 1;
    transform: translateY(-3.8433px) scale(1.0712, 1.0712);
  }
  45.2381% {
    opacity: 1;
    transform: translateY(-3.0742px) scale(1.0569, 1.0569);
  }
  46.4286% {
    opacity: 1;
    transform: translateY(-2.3201px) scale(1.043, 1.043);
  }
  47.6190% {
    opacity: 1;
    transform: translateY(-1.6176px) scale(1.03, 1.03);
  }
  48.8095% {
    opacity: 1;
    transform: translateY(-0.9932px) scale(1.0184, 1.0184);
  }
  50% {
    opacity: 1;
    transform: translateY(-0.4634px) scale(1.0086, 1.0086);
  }
  51.1905% {
    opacity: 1;
    transform: translateY(-0.0361px) scale(1.0007, 1.0007);
  }
  52.3810% {
    opacity: 0.9947;
    transform: translateY(0.2886px) scale(0.9947, 0.9947);
  }
  53.5714% {
    opacity: 0.9904;
    transform: translateY(0.5161px) scale(0.9904, 0.9904);
  }
  54.7619% {
    opacity: 0.9878;
    transform: translateY(0.6565px) scale(0.9878, 0.9878);
  }
  55.9524% {
    opacity: 0.9866;
    transform: translateY(0.7226px) scale(0.9866, 0.9866);
  }
  57.1429% {
    opacity: 0.9865;
    transform: translateY(0.7288px) scale(0.9865, 0.9865);
  }
  58.3333% {
    opacity: 0.9872;
    transform: translateY(0.6895px) scale(0.9872, 0.9872);
  }
  59.5238% {
    opacity: 0.9885;
    transform: translateY(0.6184px) scale(0.9885, 0.9885);
  }
  60.7143% {
    opacity: 0.9902;
    transform: translateY(0.528px) scale(0.9902, 0.9902);
  }
  61.9048% {
    opacity: 0.9921;
    transform: translateY(0.4288px) scale(0.9921, 0.9921);
  }
  63.0952% {
    opacity: 0.9939;
    transform: translateY(0.3292px) scale(0.9939, 0.9939);
  }
  64.2857% {
    opacity: 0.9956;
    transform: translateY(0.2357px) scale(0.9956, 0.9956);
  }
  65.4762% {
    opacity: 0.9972;
    transform: translateY(0.1525px) scale(0.9972, 0.9972);
  }
  66.6667% {
    opacity: 0.9985;
    transform: translateY(0.0822px) scale(0.9985, 0.9985);
  }
  67.8571% {
    opacity: 0.9995;
    transform: translateY(0.026px) scale(0.9995, 0.9995);
  }
  69.0476% {
    opacity: 1;
    transform: translateY(-0.0164px) scale(1.0003, 1.0003);
  }
  70.2381% {
    opacity: 1;
    transform: translateY(-0.0459px) scale(1.0008, 1.0008);
  }
  71.4286% {
    opacity: 1;
    transform: translateY(-0.0641px) scale(1.0012, 1.0012);
  }
  72.6190% {
    opacity: 1;
    transform: translateY(-0.0729px) scale(1.0013, 1.0013);
  }
  73.8095% {
    opacity: 1;
    transform: translateY(-0.0743px) scale(1.0014, 1.0014);
  }
  75.0000% {
    opacity: 1;
    transform: translateY(-0.0703px) scale(1.0013, 1.0013);
  }
  76.1905% {
    opacity: 1;
    transform: translateY(-0.0627px) scale(1.0012, 1.0012);
  }
  77.3810% {
    opacity: 1;
    transform: translateY(-0.053px) scale(1.001, 1.001);
  }
  78.5714% {
    opacity: 1;
    transform: translateY(-0.0425px) scale(1.0008, 1.0008);
  }
  79.7619% {
    opacity: 1;
    transform: translateY(-0.0321px) scale(1.0006, 1.0006);
  }
  80.9524% {
    opacity: 1;
    transform: translateY(-0.0226px) scale(1.0004, 1.0004);
  }
  82.1429% {
    opacity: 1;
    transform: translateY(-0.0143px) scale(1.0003, 1.0003);
  }
  83.3333% {
    opacity: 1;
    transform: translateY(-0.0074px) scale(1.0001, 1.0001);
  }
  84.5238% {
    opacity: 1;
    transform: translateY(-0.002px) scale(1, 1);
  }
  85.7143% {
    opacity: 1;
    transform: translateY(0.0019px) scale(1, 1);
  }
  86.9048% {
    opacity: 0.9999;
    transform: translateY(0.0045px) scale(0.9999, 0.9999);
  }
  88.0952% {
    opacity: 0.9999;
    transform: translateY(0.006px) scale(0.9999, 0.9999);
  }
  89.2857% {
    opacity: 0.9999;
    transform: translateY(0.0066px) scale(0.9999, 0.9999);
  }
  90.4762% {
    opacity: 0.9999;
    transform: translateY(0.0065px) scale(0.9999, 0.9999);
  }
  91.6667% {
    opacity: 0.9999;
    transform: translateY(0.006px) scale(0.9999, 0.9999);
  }
  92.8571% {
    opacity: 0.9999;
    transform: translateY(0.0053px) scale(0.9999, 0.9999);
  }
  94.0476% {
    opacity: 0.9999;
    transform: translateY(0.0043px) scale(0.9999, 0.9999);
  }
  95.2381% {
    opacity: 0.9999;
    transform: translateY(0.0034px) scale(0.9999, 0.9999);
  }
  96.4286% {
    opacity: 1;
    transform: translateY(0.0025px) scale(1, 1);
  }
  97.6190% {
    opacity: 1;
    transform: translateY(0.0017px) scale(1, 1);
  }
  98.8095% {
    opacity: 1;
    transform: translateY(0.001px) scale(1, 1);
  }
  100% {
    opacity: 1;
    transform: translateY(0.0004px) scale(1, 1);
  }
}

    </style>
