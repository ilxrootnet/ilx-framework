
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