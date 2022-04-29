<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('category_list','ApiController@category_list');
Route::get('subcategory_list','ApiController@subcategory_list');
Route::get('graph_list','ApiController@graph_list')->middleware('auth:api');
Route::get('collectionList','ApiController@collectionList')->middleware('auth:api');
Route::get('Collectiondetail','ApiController@Collectiondetail')->middleware('auth:api');

Route::post('register','ApiController@register');
Route::post('login','ApiController@login');
Route::post('verifyUser','ApiController@verifyUser');

Route::post('socialLogin','ApiController@socialLogin');

Route::get('profile','ApiController@profile')->middleware('auth:api');
Route::post('update_profile','ApiController@profile')->middleware('auth:api');

Route::post('update_device','ApiController@update_device')->middleware('auth:api');
Route::post('notificationRead','ApiController@notificationRead')->middleware('auth:api');
Route::post('UnreadReadCount','ApiController@UnreadReadCount')->middleware('auth:api');

Route::get('check_username','ApiController@check_username')->middleware('auth:api');  
Route::post('changePassword','ApiController@changePassword')->middleware('auth:api');
Route::post('forgotPassword','ApiController@forgotPassword');
Route::post('resetPassword','ApiController@resetPassword');

Route::post('create_post','ApiController@createPost')->middleware('auth:api');
Route::post('sharePost','ApiController@sharePost')->middleware('auth:api');
Route::post('repost','ApiController@repost')->middleware('auth:api');
Route::get('post_list','ApiController@post_list')->middleware('auth:api');
Route::delete('delete_post','ApiController@deletePost')->middleware('auth:api');
Route::get('post_detail','ApiController@post_detail')->middleware('auth:api');

Route::post('comment','ApiController@commentPost')->middleware('auth:api');
Route::post('userList','ApiController@userList')->middleware('auth:api');

Route::get('block_list','ApiController@block_list')->middleware('auth:api');
Route::get('logout','ApiController@logout')->middleware('auth:api');

Route::post('like','ApiController@like')->middleware('auth:api');
Route::post('bookmark','ApiController@bookmark')->middleware('auth:api');
Route::post('follow','ApiController@follow')->middleware('auth:api');
Route::post('comment_like','ApiController@comment_like')->middleware('auth:api');
Route::post('vote','ApiController@vote')->middleware('auth:api');
Route::post('favourite','ApiController@favourite')->middleware('auth:api');

Route::get('notificationList','ApiController@notificationList')->middleware('auth:api');
Route::get('bookmarkList','ApiController@bookmarkList')->middleware('auth:api');
Route::get('get_media_list','ApiController@get_media_list')->middleware('auth:api');

Route::delete('deleteAccount','ApiController@deleteAccount')->middleware('auth:api');


Route::post('setpreferences','ApiController@setpreferences')->middleware('auth:api');
Route::get('setpreferences','ApiController@setpreferences')->middleware('auth:api');

Route::get('gallery','ApiController@gallery')->middleware('auth:api');

Route::delete('gallery','ApiController@gallery')->middleware('auth:api');

Route::post('make_default','ApiController@make_default')->middleware('auth:api');

Route::post('visibility','ApiController@visibility')->middleware('auth:api');

Route::get('match','ApiController@match')->middleware('auth:api');
Route::delete('match','ApiController@match')->middleware('auth:api');

Route::get('pending_match','ApiController@pending_match')->middleware('auth:api');

Route::post('report','ApiController@report')->middleware('auth:api');

Route::get('user_detail','ApiController@user_detail')->middleware('auth:api');

Route::get('recommend_list','ApiController@recommend_list')->middleware('auth:api');

Route::post('userNotify','ApiController@userNotify')->middleware('auth:api');



Route::get('subscriptionsList','ApiController@subscriptionsList');
Route::post('pendingSubscriptionPlan','ApiController@pendingSubscriptionPlan')->middleware('auth:api');
Route::get('cronJobForSubscreption','ApiController@cronJobForSubscreption');

Route::post('androidSubscreption','ApiController@androidSubscreption')->middleware('auth:api');
Route::post('groupChat','ApiController@groupChat')->middleware('auth:api');
Route::get('groupChatNewList','ApiController@groupChatNewList')->middleware('auth:api');
Route::get('groupChatMessageList','ApiController@groupChatMessageList')->middleware('auth:api');

Route::get('chatList','ApiController@chatList')->middleware('auth:api');
Route::post('chat','ApiController@chat')->middleware('auth:api');
Route::get('ChatMessageList','ApiController@ChatMessageList')->middleware('auth:api');
Route::get('block','ApiController@block')->middleware('auth:api');


// twilio
Route::post('chat_user', "ApiController@chat_user")->middleware('auth:api');
Route::get('chat_token','ApiController@chat_token')->middleware('auth:api');
Route::post('chat_post_event','ApiController@chat_post_event');
Route::post('chat_pre_event','ApiController@chat_pre_event');
Route::get('chat_update_uername','ApiController@chat_update_uername');
Route::post('addchatuser','ApiController@addchatuser');
Route::post('contact','ApiController@contact');

Route::get('check_pending','ApiController@check_pending');
Route::get('update_previous','ApiController@update_previous');

Route::get('notification_match_detail','ApiController@notification_match_detail')->middleware('auth:api');
// Question Answer
Route::get('question','ApiController@question')->middleware('auth:api');
Route::post('answer','ApiController@answer')->middleware('auth:api');
Route::delete('answer_delete','ApiController@answer_delete')->middleware('auth:api');

Route::post('chip','ApiController@chip')->middleware('auth:api');
Route::get('chip_list','ApiController@chip_list')->middleware('auth:api');
Route::get('chip_data_list','ApiController@chip_data_list')->middleware('auth:api');
Route::get('agoraToken','ApiController@agoraToken')->middleware('auth:api');
Route::post('createEvent','ApiController@createEvent')->middleware('auth:api');
Route::post('eventJoin','ApiController@eventJoin')->middleware('auth:api');
Route::get('eventList','ApiController@eventList')->middleware('auth:api');
Route::get('endEvent','ApiController@endEvent')->middleware('auth:api');
Route::get('cronJobForEndEvent','ApiController@cronJobForEndEvent');

Route::post('read_mark','ApiController@read_mark')->middleware('auth:api');
Route::get('follower_list','ApiController@follower_list')->middleware('auth:api');
Route::get('following_list','ApiController@following_list')->middleware('auth:api');
Route::post('userPostSearch','ApiController@userPostSearch')->middleware('auth:api');


///////////////phase 3
Route::post('becomeCreater','ApiController@becomeCreater')->middleware('auth:api');
Route::post('bankUpdate','ApiController@bankUpdate')->middleware('auth:api');

/*
Route::middleware('auth')->group(function () {
    Route::get('profile', [App\Http\Controllers\ApiController::class, 'profile'])->name('profile');
    });*/