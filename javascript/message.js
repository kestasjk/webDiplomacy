document.observe("dom:loaded", function() {
    Event.observe('message-send', 'click', function(event) {
        $('chatForm').request({
            onFailure: function() { },
            onSuccess: function(t) {
                var cbs = $("chatboxscroll");
                cbs.update(t.responseText);
                updateTimestamps();
                $('sendbox').value = '';
                cbs.scrollTop = cbs.scrollHeight;
            }
        });
        Event.stop(event); // stop the form from submitting
    });
});