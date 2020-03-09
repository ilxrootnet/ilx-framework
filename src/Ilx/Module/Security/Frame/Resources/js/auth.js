
function login(username, password, callback) {
    let dialect = getDialect(username);
    dialect.login(password, callback);
}


function register(credentials, callback) {
    let dialect = getDialect(credentials["username"]);
    dialect.register(credentials, callback);
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
            return new BasicDialect(username);
    }
}


class Dialect {

    constructor(username) {
        this.username = username;
    }

    login(password, callback)  {
        throw new Error("Method not available.");
    }

    register(credentials, callback) {
        throw new Error("Method not available.");
    }

    changePassword(credentials, callback) {
        throw new Error("Method not available.");
    }

    resetPassword() {
        throw new Error("Method not available.");
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

class BasicDialect extends Dialect {
    login(password, callback) {
        $.post('/auth/basic/login', {
            "username": this.username,
            "password": password
        }, callback);
    }

    register(credentials, callback) {
        $.post('/auth/basic/register', credentials, callback);
    }

}