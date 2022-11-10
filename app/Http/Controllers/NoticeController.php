<?php

namespace App\Http\Controllers;

use App\DataTables\NoticeBoardDataTable;
use App\Helper\Reply;
use App\Http\Requests\Notice\StoreNotice;
use App\Models\Notice;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\NoticeLike;
use App\Models\NoticeComment;
class NoticeController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.noticeBoard';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(NoticeBoardDataTable $dataTable)
    {
       
        // $check_notic = DB::table('notices')->get();
        // return view('notices.index',get_defined_vars);
        // $notice = DB::table('notices')->join('notice_likes', 'notice_likes.notice_id', '=', 'notices.id')
        // ->select('notices.*','notice_likes.likes')->get();
        $viewPermission = user()->permission('view_notice');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        $notice = Notice::with(['likes','comments'])->get();
        return $dataTable->render('notices.index',$this->data,compact('notice'));

    }
    public function likeNotices(Request $request)
    {
        $message ="";
        if($request->already==1 || $request->is_del==1){
            NoticeLike::where(["notice_id"=>$request->notice_id,"user_id"=>user()->id])->delete();
        }
        if($request->is_del==1){
            $message = "Notice dislike successfully";
        }
        else{
            $query = new NoticeLike;
            $query->user_id = user()->id;
            $query->notice_id = $request->notice_id;
            // $query->like = $request->like_type==1?1:0;
            $query->likes = 1;
            $query->save();
            $message = "Notice Liked successfully";
        }
        return response()->json(["message"=>$message,"status"=>200]);

    }
    public function deleteNotices(Request $request){
        Notice::where('id',$request->id)->delete();
        NoticeComment::where('notice_id',$request->id)->delete();
        NoticeLike::where('notice_id',$request->id)->delete();
        return redirect()->back()->with('message','Notice has been deleted successfully');
    }
    public function commentNotices(Request $request){
        $comment = new NoticeComment;
        $comment->comment = $request->comment;
        $comment->user_id = user()->id;
        $comment->notice_id = $request->notice_id;
        $comment->save();
        $all_comments = NoticeComment::where('id',$comment->id)->first();
        return response()->json(["message"=>"Comment posted successfully","status"=>200,"data"=>$all_comments,"user"=>user()]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_notice');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->teams = Team::all();
        $this->pageTitle = __('modules.notices.addNotice');

        if (request()->ajax()) {
            $html = view('notices.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'notices.ajax.create';
        return view('notices.create', $this->data);
    }

    /**
     * @param StoreNotice $request
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreNotice $request)
    {
        $this->addPermission = user()->permission('add_notice');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $notice = new Notice();
        $notice->heading = $request->heading;
        $notice->description = str_replace('<p><br></p>', '', trim($request->description));
        $notice->to = $request->to;
        $notice->department_id = $request->team_id;
        $notice->save();

        return Reply::successWithData(__('messages.noticeAdded'), ['redirectUrl' => route('notices.index')]);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->notice = Notice::with('member', 'member.user')->findOrFail($id);
        $this->viewPermission = user()->permission('view_notice');
        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->notice->added_by == user()->id)
            || ($this->viewPermission == 'owned' && in_array($this->notice->to, user_roles()))
            || ($this->viewPermission == 'both' && (in_array($this->notice->to, user_roles()) || $this->notice->added_by == user()->id))
        ));


        $readUser = $this->notice->member->filter(function ($value, $key) {
            return $value->user_id == $this->user->id && $value->notice_id == $this->notice->id;
        })->first();

        if ($readUser) {
            $readUser->read = 1;
            $readUser->save();
        }

        $this->readMembers = $this->notice->member->filter(function ($value, $key) {
            return $value->read == 1;
        });


        $this->unReadMembers = $this->notice->member->filter(function ($value, $key) {
            return $value->read == 0;
        });

        if (request()->ajax()) {
            $this->pageTitle = __('app.menu.noticeBoard');
            $html = view('notices.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'notices.ajax.show';
        return view('notices.create', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->notice = Notice::findOrFail($id);
        $this->editPermission = user()->permission('edit_notice');

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->notice->added_by == user()->id)
            || ($this->editPermission == 'owned' && in_array($this->notice->to, user_roles()))
            || ($this->editPermission == 'both' && (in_array($this->notice->to, user_roles()) || $this->notice->added_by == user()->id))
        ));

        $this->teams = Team::all();
        $this->pageTitle = __('modules.notices.updateNotice');

        if (request()->ajax()) {
            $html = view('notices.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'notices.ajax.edit';

        return view('notices.create', $this->data);

    }

    /**
     * @param StoreNotice $request
     * @param int $id
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreNotice $request, $id)
    {
        $notice = Notice::findOrFail($id);
        $this->editPermission = user()->permission('edit_notice');
        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->notice->added_by == user()->id)
            || ($this->editPermission == 'owned' && in_array($this->notice->to, user_roles()))
            || ($this->editPermission == 'both' && (in_array($this->notice->to, user_roles()) || $this->notice->added_by == user()->id))
        ));

        $notice->heading = $request->heading;
        $notice->description = str_replace('<p><br></p>', '', trim($request->description));
        $notice->to = $request->to;
        $notice->department_id = $request->team_id;
        $notice->save();

        return Reply::successWithData(__('messages.noticeUpdated'), ['redirectUrl' => route('notices.index')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notice = Notice::findOrFail($id);
        $this->deletePermission = user()->permission('delete_notice');
        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $notice->added_by == user()->id)
            || ($this->deletePermission == 'owned' && in_array($notice->to, user_roles()))
            || ($this->deletePermission == 'both' && (in_array($notice->to, user_roles()) || $notice->added_by == user()->id))
        ));

        Notice::destroy($id);
        return Reply::successWithData(__('messages.noticeDeleted'), ['redirectUrl' => route('notices.index')]);

    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);
                return Reply::success(__('messages.deleteSuccess'));
        default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_notice') != 'all');

        Notice::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

}
