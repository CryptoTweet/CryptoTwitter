var project_url="";$(function(){$("a.ajax-get").click(function(e){e.preventDefault();$.get($(this).attr("href"),function(e){if(parseInt(e)===1){window.location.href=project_url+"/users/dashboard/#friends"}else{return false}})});$("a#update_friends").click(function(e){e.preventDefault();$(this).html("Retrieving friends from Twitter...1 second please...").attr("id","");$.get(project_url+"/twitter/update_friends",function(){window.location.href=project_url+"/users/dashboard"})});$("a#update_followers").click(function(e){e.preventDefault();$(this).html("Retrieving followers from Twitter...1 second please...").attr("id","");$.get(project_url+"/twitter/update_followers",function(){window.location.href=project_url+"/users/dashboard"})});$("button#send_request").click(function(){$(this).html("Sending....").attr("disabled",true).attr("readonly",true).attr("enabled",false);var e=$("input#recipient").val();if(e>0){$.post(project_url+"/requests/send_authorization_request/"+e,{csrf_crypto:$("input[name='csrf_crypto']").val(),secret:$("input#secret").val(),answer:$("input#answer").val()},function(e){window.location.href=project_url+"/users/dashboard/"})}else{alert("Recipient not found in our DB of registered users.");window.location.href=project_url+"/users/dashboard/"}})})