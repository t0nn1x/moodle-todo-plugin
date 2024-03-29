define([
    'core/config', // lib\amd\src\config.js
    'core/modal_factory', // lib\amd\src\modal_factory.js
    'core/mustache' // lib\amd\src\mustache.js
], function (config, ModalFactory, Mustache) {
    function init() {
        console.log(config, ModalFactory, Mustache);

        var button = document.getElementById('btn_helloworld');

        button.addEventListener('click', function () {
            alert('Hello world!');
        });
    };

    return {
        init: init,
    }

});
