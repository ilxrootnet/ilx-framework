
function login(username, password) {
    let dialect = getDialect(username);
    return dialect.login(password);
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

    login(password) {
        var login_result;
        $.ajax({
            type: "POST",
            url: "/auth/remote/login",
            data: $.param({
                "username": this.username,
                "password": password
            }),
            success: function(result) {
                login_result = result;
            },
            dataType: "html"
        });

        return login_result;
    }
}