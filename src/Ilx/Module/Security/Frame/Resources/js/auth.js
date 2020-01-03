
function login(username, password, callback) {
    let dialect = getDialect(username);
    dialect.login(password, callback);
}


/**
 *
 * @param {string} username
 * @returns {Dialect}
 */
function getDialect(username) {
    var auth_dialect = "";
    $.ajax({
        type: "POST",
        url: "/auth/dialect",
        data: JSON.stringify({
            "username": username
        }),
        success: function(result) {
            auth_dialect = result["data"]["dialect"];
        },
        dataType: "json",
        async:false
    });

    switch (auth_dialect) {
        case "auth_remote":
            return new RemoteDialect(username);
        case "auth_basic":
            break;
    }
}


class Dialect {

    constructor(username) {
        this.username = username;
    }

    login(password) {
        return "";
    }
}

class RemoteDialect extends Dialect {

    login(password, callback) {
        $.post('/auth/remote/login', {
            "username": this.username,
            "password": password
        }, callback);
    }
}

class LoginLayout {

    constructor() {
        this.init();
    }

    init() {
        var _self = this;
        // ablak alaphelyzetbe méretezése
        let w=$(window).width();
        let h=$(window).height();

        $(window).resize(function(){
            let w=$(window).width();
            let h=$(window).height();
            _self.onResize(w,h);
        });        
        _self.onResize(w,h);
        // fel scrollozás
        $(window).scrollTop(0);
        // hide-screen eltűntetése
        $("#hide-screen").addClass('hidden');
        setTimeout(function() {
            $("#hide-screen").css({'display':'none'});
        },300);
        // hibaüzenet kezelése
        _self.failBox=$("body > .login-wrapper > .form-wrapper > .fail-box");

        // input mezők keyup eseményei: ha van hiba jelzés, akkor azt eltűntetjük
        $("input[name=username]").unbind('keypress');
        $("input[name=username]").keypress(function() {
            _self.failBoxHide();
        });
        $("input[name=password]").unbind('keypress');
        $("input[name=password]").keypress(function() {
            _self.failBoxHide();
        });

        // enter gomb jelszó mezőben = belépés gomb
        $('input[name=password]').keypress(function(e){
            if (e.which=='13') $('button.login').click();
        });        
    }

    onResize(w, h) {
        $("body > .login-wrapper").css({'height':h+'px'});
    }


    failBoxHide() {
        if ($(this.failBox).hasClass("show")) {
            $(this.failBox).removeClass("show");
            $(this.failBox).animate({
                'top': 200
            },300,'easeInBack');
        }
    }

    failBoxShow() {
        $(this.failBox).addClass('show');
        $(this.failBox).animate({
            'top': 300
        },700,'easeOutBounce');        
    }

    failBoxSet(v) {
        $(this.failBox).html(v)
    }
}