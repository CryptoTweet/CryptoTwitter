var project_url='';var timer;function load_friends(){if($("select#recipient").attr("id")!==undefined){if($("select#recipient option").length===0){$.getJSON(project_url+"/twitter/get_friends",function(res){$.each(res,function(idx,item){if(item.twitter_id===0){$('<option value="'+item.twitter_id+'" selected="selected">'+item.username+'</option>').appendTo("#recipient")}else{$('<option value="'+item.twitter_id+'">'+item.username+'</option>').appendTo("#recipient")}});if($("select#recipient").find('option').length===0){$('<option value="-1">No recipients found</option>').appendTo("#recipient")}return})}}}function load_etweets(){if($('#tweetfeed').attr("id")!==undefined){$.get(project_url+'/twitter/etweets',function(data){$('#tweetfeed').empty();if(data!==0){$(data).appendTo("#tweetfeed");init_delete();init_reply();init_retweet()}else{$("#tweetfeed").html('<div class="row-fluid">There are tweets for you yet. Get authorized by contacting your friends.</div>')}})}}function init_retweet(){$('a.retweet').unbind('click');$("a.retweet").click(function(e){e.preventDefault();if($(this).attr("data-id")!==undefined&&$(this).attr("data-id")>0){$.get(project_url+"/twitter/retweet/"+$(this).attr("data-id"),function(res){load_etweets()})}else{}return false})}function init_reply(){$("a.reply").unbind("click");$("a.reply").click(function(e){e.preventDefault();var id=$(this).attr("data-id");if(parseInt(id)>0){$("div.reply").filter("[data-id="+id+"]").toggleClass("hide");var parent=$("div.reply").filter("[data-id="+id+"]");var recipient=$(this).attr("data-id");if($(parent).hasClass("hide")===false&&parseInt(recipient)>0){$(parent).find("button#reply_to_tweet").unbind('click');$(parent).find("button#reply_to_tweet").click(function(){$(this).html("Sending..").attr('enabled',false).attr('disabled',true).attr('readonly',true).delay(100);if($(parent).find("textarea#replytweet").val().length>2){$.post(project_url+"/twitter/reply/",{tweet:$(parent).find("textarea#replytweet").val(),csrf_crypto:$("input[name='csrf_crypto']").val(),recipient:0,tweet_also:0,parent:recipient},function(result){if(parseInt(result)===1){$(parent).find("textarea#replytweet").val("")}else{$(parent).find("textarea#replytweet").val("Tweet could not be send. Try again later.")}$(parent).find("span.charcount").keyup();$(parent).find("button#reply_to_tweet").html("ETweet!").attr('enabled',true).attr('disabled',false).attr('readonly',false).delay(100);return false})}});$(parent).find("textarea#replytweet").keyup(function(){if((130-$(this).val().length)>=0){$(parent).find("span.charcount").html(130-$(this).val().length)}else{$(parent).find("textarea#replytweet").val($(parent).find("textarea#replytweet").val().slice(0,130))}});$(parent).unbind('focusin');$(parent).focusin(function(){timer.stop()});$(parent).unbind('focusout');$(parent).focusout(function(){timer.set({time:10000,autostart:true});if($(parent).find("textarea#replytweet").val().length===0){$(parent).toggleClass("hide")}})}}})}function init_delete(){$('a.delete').unbind('click');$("a.delete").click(function(e){e.preventDefault();if($(this).attr("data-id")!==undefined&&$(this).attr("data-id")>0){$.get(project_url+"/twitter/delete/"+$(this).attr("data-id"),function(res){load_etweets()})}else{}return false})}$(function(){if($("textarea#tweet").attr("id")!==undefined){$("textarea#tweet").focusin(function(){$("textarea#tweet").css("height","85px")});$("textarea#tweet").focusout(function(){$("textarea#tweet").css("height","25px")})}if($("select#recipient").attr("id")!==undefined){$("select#recipient").next("a").click(function(e){e.preventDefault();$(this).html('Loading..');$.get(project_url+"/twitter/update_followers/",function(){window.location.href='/projects/CryptoTwitter/'});return false});load_friends();$("select#recipient").click(function(){load_friends()})}if($("textarea#tweet").attr("id")!==undefined){$("textarea#tweet").keyup(function(){if((130-$(this).val().length)>=0){$("span#charcount").html(130-$(this).val().length)}else{$("textarea#tweet").val($("textarea#tweet").val().slice(0,130))}});$("button#send_tweet").click(function(){$(this).html("Sending..").attr('enabled',false).attr('disabled',true).attr('readonly',true).delay(100);if($("textarea#tweet").val().length>0&&$("input[name='csrf_crypto']").val()!==""&&$("select#recipient").val()>=0){$.post(project_url+"/twitter/send_tweet/",{tweet:$("textarea#tweet").val(),csrf_crypto:$("input[name='csrf_crypto']").val(),recipient:$("select#recipient").val(),tweet_also:($("input#tweet_also:checked").val()!==undefined?1:0)},function(result){if(parseInt(result)===1){$("textarea#tweet").val("")}else{$("textarea#tweet").val("Tweet could not be send. Try again later.")}$("span#charcount").keyup();$("button#send_tweet").html("ETweet!").attr('enabled',true).attr('disabled',false).attr('readonly',false).delay(100);return false})}})}if($.timer!==undefined){timer=$.timer(function get_tweets(){if($('#tweetfeed')!==undefined){load_etweets();init_delete();init_reply();init_retweet()}});timer.set({time:10000,autostart:true})}load_etweets();init_delete();init_reply();init_retweet();$("a").mousedown(function(e){if($(this).attr("rel")&&$(this).attr("rel")==='external'){e.preventDefault();if($(this).attr('data-goto')&&$(this).attr('data-goto')!==""){window.location.href=project_url+'/'+$(this).attr('data-goto')}else{return false}}return false});$("a").click(function(e){if($(this).attr("rel")&&$(this).attr("rel")==='external'){e.preventDefault();var h=0;var w=0;if($(this).attr("data-height")&&$(this).attr("data-height")!==""){h=$(this).attr("data-height")}if($(this).attr("data-width")&&$(this).attr("data-width")!==""){w=$(this).attr("data-width")}var win=window.open($(this).attr("href"),'_blank','height='+h+',width='+w);win.focus();return false}})});