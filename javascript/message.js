document.observe("dom:loaded", function() {
    // The chatbox queues this script but isn't printed on every board.php view (e.g. viewArchive=...)
    if ( $('message-send') == null ) return;

    Event.observe('message-send', 'click', function(event) {
        $('chatForm').request({
            onFailure: function() {
                alert("Error while sending message");
            },
            onException: function() {
                alert("Error contacting server");
            },
            onSuccess: function(t) {
                if (t.status == 200) {
                    var cbs = $("chatboxscroll");
                    cbs.update(t.responseText);
                    updateTimestamps();
                    $('sendbox').value = '';
                    cbs.scrollTop = cbs.scrollHeight;
                }else{
                    alert("Error while sending message");
                }
            }
        });
        Event.stop(event); // stop the form from submitting
    });
});