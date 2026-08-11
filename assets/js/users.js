const URL = "../ajax/users.php"

function request(action, data = {}) {
    $.ajax({
        url: URL,
        type: "POST",
        data: {
            action,
            ...data
        },
        error: function(xhr) {
            console.error(xhr.responseText);
        }
    })
}

request("list").done(users => {
    console.table(users)
})