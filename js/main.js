$(document).ready(function(){
	
    $('#alt-search-button').hide();
    $('#search-button').css('display','inline-block').click(function(){
        var field = $('#search-field').val();
		
        if (field.length != 0) {
            $('#searchform').submit();
        }
    });
	
    $('#search-overlay').show().click(function(){
        $('#search-field').focus();
        $(this).hide();
    });
	
	
    var field = $('#search-field').val();
    if (field.length > 0)
        $('#search-overlay').hide();
		
    $('#search-field').focus( function(){
        $('#search-overlay').hide();
    });
	
    $('.popup-close').click(function(){
        $(this).parent().parent().hide();
        $('#overlay').hide();
        return false;
    });
	
    $('#login, #comments-warp .login').click(function(){
        return showbox('#box-login');
    });
	
    $('#registration, #comments-warp .registration').click(function(){
        return showbox('#box-registration');
    });

    $('#overlay').click(function(e){
        if ($(e.target).attr('id') == 'overlay'){
            $(this).hide();
            $('.popup-box').hide();
        }
    });
	
    /* article rate */
    $('.article-dislike').click(function(){
        change_article_rate(-1);
        return false;
    });
    $('.article-like').click(function(){
        change_article_rate(1);
        return false;
    });
	
    /* show comment form */
    $('#comments-warp .answer-for-comment').live('click',function(){
        if (!$(this).data('clicked')){
            if (!$(this).data('clicked',true).parent().parent().data('clicable')){
                var id = $(this).parent().parent().attr('id');
                $(this).parent().after('<div class="comment-answer-form"><form action="/articles/5/?new_comment" method="post" class="comment-form"><div><input name="name" value="Ваше имя"><br/><br/><textarea name="comment" class="comment-answer-textarea" onkeypress="if(event.keyCode==10||(event.ctrlKey && event.keyCode==13))$(this.form).submit();"></textarea></div><input type="submit" value="добавить комментарий" class="comment-answer-submit" /><span class="loader"></span> Ctrl + Enter<input type="hidden" name="answerto" value="'+id+'" /><input type="hidden" name="article_id" value="'+article_id+'" /></form></div>').parent().data('clicable', true).find('textarea').focus();
            } else {
                $(this).parent().next().show().find('textarea').focus();
            }
        } else {
            $(this).data('clicked',false).parent().next().hide();
        }
        return false;
    });
	
    /* send comment */
    $('.comment-form').live('submit',function(){								  
        if ($(this).data('commentsent'))
            return false;		
        var text = $(this).find('textarea').val();
        var name = $(this).find('input[name=name]').val();
        if(name.length == 0) {
            alert('Имя не должно быть пустым');
            return false;
        }
        if(text.length == 0) {
            alert('Комментарий не должен быть пустым');
            return false;
        }
        var answerto = $(this).find('input[name=answerto]').val();
        var form = $(this);
        form.data('commentsent', true);		
        var data = form.serialize();
		
        $.ajax({
            url: 'ajax/CommentsPlugin.php',
            type: 'post',
            data: data,
            beforeSend: function(){
                form.find('.loader').css('display','inline-block');
            },
            dataType: 'json',
            success: function(data){
				
                form.parent().prev().find('.answer-for-comment').data('clicked',false);		
                $('#'+answerto).data('clicable', false).parent().find();
                if (answerto=='comments-warp') {
                    answerto = 'comments';
                }
                var comment_number = data.id;
                $('#'+answerto).append(data.html).find('#comment_'+comment_number).fadeIn(500);
                form.parent().remove();
            },
            timeout: 6000,
            complete: function(){
                form.data('commentsent', false);
                $('#comments-warp .loader').hide();
            },
            error: function(){
                alert('Не удалось отправить коммент');
            }
        });
        return false;
    });
	
    /* ie6 fix */
    if ( $.browser.msie && $.browser.version <= 6.0 ) {
        $('#selectable-link').hover(function(){
            $('#selectable-menu').show();
        }, function(){
            $('#selectable-menu').hide();
        });
		
        $('.comment-rating').hover(function(){
            $(this).find('a').each(function(){
                $(this).css('display','block');
            });
        }, function(){
            $(this).find('a').each(function(){
                $(this).css('display','none');
            });
        });
    }
        
    /*ONFOCUS VIDEOADD*/
    $("input[name='link']").focus(function () {
        $(this).val('');
    });
	
    /* IMAGE MINI GALLERY */
    var bigimage = $('#big-image');
    if ( bigimage !== undefined ) {
		
        var gallery = new Array();
		
        $('#article-mini-images .article-gallery-mini img').each(function(){
            var number =  gallery.length;
            gallery[number] = [];
            var adress = $(this).attr('src');
            gallery[number].url = adress.replace(RegExp(/\/gallery/), '');
            gallery[number].title = $(this).attr('title');
            if ( gallery[number].title === undefined )
                gallery[number].title = '';
            $(this).attr('id', 'image-'+number );
        });
		
        bigimage.click(function(){

            if (bigimage.data('thisimage')) {
                var imagenum = +bigimage.data('thisimage') + 1;
            } else {
                var imagenum = 1;
            }
			
            if (gallery.length == imagenum)
                imagenum = 0;
			
            replaceimage(imagenum);
        }).addClass('pointer');
		
        $('#article-mini-images .article-gallery-mini').click(function(){
            var imagenum = /\d+/.exec($(this).children(':first').attr('id'));
            replaceimage(imagenum);
            return false;
        });
		
        function replaceimage(imagenum) {
            $('#photo-title').html(gallery[imagenum].title);
            $('#big-image').attr('src',gallery[imagenum].url);
            bigimage.data('thisimage',imagenum);
            $('.activeimage').removeClass('activeimage');
            $('#image-'+imagenum).addClass('activeimage');
        }
    }
	
    /*COMMENTS RATING*/
    $('.comment-like').live('click',function(){
        var comment_id = $(this).parent().attr("id");
        var rating = $(this).parent().find('.rating-rate').text();
        var votecomment = 1;
        $.ajax({
            url: 'ajax/CommentsRatingPlugin.php',
            type: 'post',
            data: ({
                comment_id : comment_id, 
                votecomment : votecomment, 
                rating : rating
            }),
            dataType: 'text',
            success: function(data){

                if (data < 0)
                    $('#'+comment_id).addClass('comment-rating-red');
                if (data > 0)
                    $('#'+comment_id).addClass('comment-rating-green');
                if (data == 0) {
                    $('#'+comment_id).removeClass('comment-rating-red');
                    $('#'+comment_id).addClass('comment-rating');
                }
                $('#'+comment_id).find('.rating-rate').text(data);
            }
        });
		
        return false;
				
    });
	
    /*COMMENTS RATING*/
    $('.comment-dislike').live('click',function(){
        var comment_id = $(this).parent().attr("id");
        var rating = $(this).parent().find('.rating-rate').text();
        var votecomment = -1;
        $.ajax({
            url: 'ajax/CommentsRatingPlugin.php',
            type: 'post',
            data: ({
                comment_id : comment_id, 
                votecomment : votecomment, 
                rating : rating
            }),
            dataType: 'text',
            success: function(data){

                if (data < 0)
                    $('#'+comment_id).addClass('comment-rating-red');
                if (data > 0)
                    $('#'+comment_id).addClass('comment-rating-green');
                if (data == 0) {
                    $('#'+comment_id).removeClass('comment-rating-green');
                    $('#'+comment_id).addClass('comment-rating');
                }
                $('#'+comment_id).find('.rating-rate').text(data);
            }

        });

        return false;

    });

    article_voted = false;
	
    //EDIT USER
    $('#useredit').submit(function(){

        var name = $("input[name='name']").val();
        var day = $("select[name='day'] option:selected").val();
        var month = $("select[name='month'] option:selected").val();
        var year = $("select[name='year'] option:selected").val();
        var fb = $("input[name='fb']").val();
        var tw = $("input[name='tw']").val();
        var vk = $("input[name='vk']").val();
        var site = $("input[name='site']").val();
        var blog = $("input[name='blog']").val();
        var about = $("#aboutme").val();
		
        if(!ValidURL(vk)) {
            alert('Некорректно заполнено поле "vkontakte"');
            return false;
        }
		
        if(!ValidURL(site)) {
            alert('Некорректно заполнено поле "Мой сайт"');
            return false;
        }
		
        if(!ValidURL(blog)) {
            alert('Некорректно заполнено поле "Адрес блога"');
            return false;
        }
		
        if(!ValidURL(fb)) {
            alert('Некорректно заполнено поле "facebook"');
            return false;
        }

        if(!ValidURL(tw)) {
            alert('Некорректно заполнено поле "twitter"');
            return false;
        }

        $.ajax({
            url: 'ajax/UserEdit.php',
            type: 'post',
            data: {
                name : name,
                day : day,
                month : month,
                year : year,
                fb : fb,
                tw : tw,
                vk : vk,
                site :site,
                blog : blog,
                about : about
            },
            dataType: 'text',
            success: function(data){
                if(data == 1) {
                    alert('Данные успешно изменены!');
                } else if(data == 2) {
                    alert('Вы не внесли изменений в данные!');
                }else {
                    alert('Данные не были изменены!');
                }
            }
        });
		
        return false;
    });

    // CHANGE PASSWORD
    $('#changePassword').submit(function(){

        var password = $("input[name='password']").val();
        var password2 = $("input[name='password2']").val();

        if (password != password2) {
            alert('Пароли не идентичны!');
            return false;
        }

        if ( password.length < 6) {
            alert('Пароль должен состоять минимум из 6 символов!');
            return false;
        }

        $.ajax({
            url: 'ajax/ChangePassword.php',
            type: 'post',
            data: {
                password : password
            },
            dataType: 'text',
            success: function(data){
                if(data > 0) {
                    alert('Пароль успешно изменён!');
                } else {
                    alert('Пароль не изменён!');
                }
            }
        });

        return false;
    });

    //DELETE COMMENT
    $('.deletecomment').live('click',function(){
        var id_comment = $(this).attr("id");
        $.ajax({
            url: 'ajax/CommentDelete.php',
            type: 'post',
            data: {
                id_comment : id_comment
            },
            dataType: 'json',
            success: function(data){
                if(data.id_delete > 0) {
                    $('#commenttext_'+data.id_delete).toggleClass('grey');
                    $('#commentfooter_'+data.id_delete).find('.deletecomment:first').hide();
                    $('#commentfooter_'+data.id_delete).find('.answer-for-comment').after(data.deletehtml);
                }
            }
        });
        return false;
    });

    /* HINT IN INPUT FIELDS */
    var typetext = $('#popup-warp .typetext');

    typetext.each(function(){
        var value = $(this).val();
        if (value.length > 0 )
            $(this).prev().hide();
    });

    typetext.focusin(function(){
        $(this).prev().hide();
    });

    typetext.focusout(function(){
        var value = $(this).val();
        if (value.length == 0 )
            $(this).prev().show();
    });


    $('.login-hint').click(function(){
        $(this).hide().next().focus();
    });

    /* REGISTRATION CHECK */
    var registration = {

        passcheckstatus: false,

        passckeck: function () {

            if(registration.passcheckstatus)
                registration.passagainckeck();

            if ( ValidPassword( $('#password').val() ) ) {
                signform.fieldright($('#password'));
                return true;
            } else {
                signform.fieldwrong($('#password'));
                return false;
            }
        },

        passagainckeck: function () {
            registration.passcheckstatus = true;
            if ( !ValidPassword($('#passwordagain').val() ) || $('#passwordagain').val() != $('#password').val() ) {
                signform.fieldwrong($('#passwordagain'));
                return false;
            } else {
                signform.fieldright($('#passwordagain'));
                return true;
            }
        }
    };

    var signform = {

        fieldwrong: function(field) {
            field.parent().parent().next().children(':first').hide().next().show();
        },

        fieldright: function(field) {
            field.parent().parent().next().children(':first').show().next().hide();
        },

        emailcheck: function( input ) {
            if(ValidEmail( input.val() )) {
                signform.fieldright(input);
                return true;
            } else {
                signform.fieldwrong(input);
                return false;
            }
        }
    };

    var authorization = {

        passwordcheck: function( input ) {
            if(ValidPassword( input.val() )) {
                signform.fieldright(input);
                return true;
            } else {
                signform.fieldwrong(input);
                return false;
            }
        }
    };

    /* AUTHORIZATION CHECK */
    $('#signinform').submit(function(){

        if ( !signform.emailcheck( $('#signin-login')) )
            return false;

        if ( !authorization.passwordcheck( $('#signin-password')) )
            return false;

        var login = $("#signin-login").val();
        var password = $("#signin-password").val();

        $.ajax({
            url: '/ajax/AuthValidate.php',
            type: 'post',
            data: {
                login : login,
                password : password
            },

            dataType: 'text',
            success: function(data){

                if(data == 0) {
                    $('#box-login .warning').show();
                } else {
                    window.location = window.location;
                }
            }
        });

        return false;
    });

    $('#email').focusout(function(){
        signform.emailcheck( $('#email') );
    });

    $('#password').focusout(function(){
        registration.passckeck();
    });

    $('#passwordagain').focusout(function(){
        registration.passagainckeck();
    });

    $('#registration-form').submit(function(){
        var result = [signform.emailcheck( $('#email')), registration.passckeck(), registration.passagainckeck() ];
        return result[0] && result[1] && result[2];
    });

    $('#email').focusout(function(){
        signform.emailcheck( $('#email') );
    });

    $('#signin-login').focusout(function(){
        signform.emailcheck( $('#signin-login') );
    });
	
    $('#signin-password').focusout(function(){
        authorization.passwordcheck( $('#signin-password') );
    });
        
    /* QUESTIONS */
    
    $(".add-answer").click( function(){
        $(this).next().toggle();
        return false;
    })
    $('.questions-header').click( function(){
        
        $('.question-form').hide();
        $('.answers').slideUp('normal');
        $(this).parent().next().slideDown('normal');
        $('.questions-header').removeClass('question-selected');
        $(this).addClass('question-selected');
        
        return false;
    })
    
    $('.delete-answer').click(function(){
            
        var id_delete_answer = $(this).attr('id');

        $.ajax({
            url: '/ajax/Questions.php',
            type: 'post',
            data: {
                task : 'DELETE_ANSWER',
                id_answer : id_delete_answer
            },
            dataType: 'text',
            success: function(data){
                if(data == 0) {
                    alert('Произошла ошибка');
                } else {
                    $('#'+id_delete_answer).parent().prev().hide();
                    $('#'+id_delete_answer).parent().hide();
                }
            }
        });
        return false;
    })

    $('.answer-form').submit(function(){
            
        var text_answer = $(this).find('.answer-textarea').val();
        if (text_answer.length == 0) {
            alert('Вы не заполнили поле');
            return false;
        }
        var id_question2answer = $(this).find('input[name=id_question]').val();
        var id_answers = $(this).parent().parent().parent().attr('id');
        var noanswer = $(this).parent().parent().parent().find('.noanswers');
   
        $.ajax({
            url: '/ajax/Questions.php',
            type: 'post',
            data: {
                task : 'ADD_ANSWER',
                text : text_answer,
                id_question : id_question2answer
            },
            dataType: 'text',
            success: function(data){
                if(data == 0) {
                    alert('Произошла ошибка');
                } else {
                    $('#'+id_answers).prepend(data);
                    noanswer.hide();
                }
            }
        });
        //$(this).parent().hide();
        $('.answer-textarea').val('');
        return false;
    })
});
/* END JQUERY LOAD EVENT */

function change_article_rate( rate ) {

	if ( article_voted ) {
		return false;
	}
	
	article_voted = true;
	
	var rating = $('.article-like-num').text();
	
	data = 'rate='+rate+'&article_id='+article_id+'&rating='+rating;
	$.ajax({
		url: 'ajax/ArticlesRating.php',
		type: 'post',
		data: data,
		dataType: 'text',
		success: function(data){
			$('.article-like-num').text(data);
		}
	});
}

function ValidURL(url){
	if (url == '') return true;
	return /^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/.test(url);
}

function ValidEmail(email){
	return /^([-_a-z0-9]+\.?)*[a-z0-9]@([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)+[a-z]{2,6}$/i.test(email);
}

function ValidPassword(pass) {
	return pass.length >= 5;
}

/* FUNTIONS NEEDED AFLER LOAD JQUERY */
function showbox(boxname){
		
	if ( $.browser.msie && $.browser.version <= 6.0 ) {
	
		if (document.documentElement && document.documentElement.scrollTop) {
			var scr = document.documentElement.scrollTop;
		} else if(window.innerHeight){
			var scr = window.scrollY;
		} else if (document.body && document.body.scrollTop ){
			var scr = document.body.scrollTop;alert(scr);
		} else {
			var scr = 0;
		}		
	
		$(boxname).css('top', scr );
		$('#overlay').css('height', $('body').height());
	}
	
	$(boxname).show();
	$('#overlay').show();
	
	return false;
}