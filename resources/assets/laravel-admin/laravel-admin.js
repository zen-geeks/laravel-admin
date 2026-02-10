toastr.options = {
    closeButton: true,
    progressBar: true,
    showMethod: 'slideDown',
    timeOut: 4000
};

$.pjax.defaults.timeout = 5000;
$.pjax.defaults.maxCacheLength = 0;
$(document).pjax('a:not(a[target="_blank"])', {
    container: '#pjax-container'
});

NProgress.configure({parent: '#app'});

$(document).on('pjax:timeout', function (event) {
    event.preventDefault();
})

$(document).on('submit', 'form[pjax-container]', function (event) {
    let form = $(this);

    if (form.parent('#filter-box').length === 0) {
        $.pjax.submit(event, '#pjax-container');
        return;
    }

    // removing empty values in grid filters
    event.preventDefault();

    let url = form.attr('action'),
        data = {};

    form.serializeArray().forEach(function (item) {
        if (item.value === '')
            return;

        if (data[item.name] !== undefined) {
            if (Array.isArray(data[item.name])) {
                data[item.name].push(item.value);
            } else {
                data[item.name] = [data[item.name], item.value];
            }
        } else {
            data[item.name] = item.value;
        }
    });

    if ($.param(data))
        url += (url.indexOf('?') === -1 ? '?' : '&') + $.param(data);

    $.pjax({
        url: url,
        container: '#pjax-container',
        type: (form.attr('method') || 'GET').toUpperCase()
    });
});

$(document).on("pjax:popstate", function () {

    $(document).one("pjax:end", function (event) {
        $(event.target).find("script[data-exec-on-popstate]").each(function () {
            $.globalEval(this.text || this.textContent || this.innerHTML || '');
        });
    });
});

$(document).on('pjax:send', function (xhr) {
    if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
        $submit_btn = $('form[pjax-container] :submit');
        if ($submit_btn) {
            $submit_btn.button('loading')
        }
    }
    NProgress.start();
});

$(document).on('pjax:complete', function (xhr) {
    if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
        $submit_btn = $('form[pjax-container] :submit');
        if ($submit_btn) {
            $submit_btn.button('reset')
        }
    }
    NProgress.done();
    $.admin.grid.selects = {};
});

$(document).click(function () {
    $('.sidebar-form .dropdown-menu').hide();
});

$(function () {
    $('.nav-sidebar').on('click', '.nav-item:not(.has-treeview) > .nav-link', function () {
        $('li.nav-item.active').removeClass('active');
        $(this).parent('li.nav-item').addClass('active');
    });
    var menu = $('.nav-sidebar a.nav-link[href$="' + (location.pathname + location.search + location.hash) + '"]').parent().addClass('active');
    menu.parents('.has-treeview').addClass('menu-open');
    menu.parent('li.nav-item').addClass('active');

    $('[data-toggle="popover"]').popover();

    // Sidebar form autocomplete
    $('.sidebar-form .autocomplete').on('keyup focus', function () {
        var $menu = $('.sidebar-form .dropdown-menu');
        var text = $(this).val();

        if (text === '') {
            $menu.hide();
            return;
        }

        var regex = new RegExp(text, 'i');
        var matched = false;

        $menu.find('li').each(function () {
            if (!regex.test($(this).find('a').text())) {
                $(this).hide();
            } else {
                $(this).show();
                matched = true;
            }
        });

        if (matched) {
            $menu.show();
        }
    }).click(function(event){
        event.stopPropagation();
    });

    $('.sidebar-form .dropdown-menu li a').click(function (){
        $('.sidebar-form .autocomplete').val($(this).text());
    });
});

$(window).scroll(function() {
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        $('#totop').fadeIn(500);
    } else {
        $('#totop').fadeOut(500);
    }
});

$('#totop').on('click', function (e) {
    e.preventDefault();
    $('html,body').animate({scrollTop: 0}, 500);
});

(function ($) {

    var Grid = function () {
        this.selects = {};
    };

    Grid.prototype.select = function (id) {
        this.selects[id] = id;
    };

    Grid.prototype.unselect = function (id) {
        delete this.selects[id];
    };

    Grid.prototype.selected = function () {
        var rows = [];
        $.each(this.selects, function (key, val) {
            rows.push(key);
        });

        return rows;
    };

    $.fn.admin = LA;
    $.admin = LA;
    $.admin.swal = function (options) {
        if (options && options.type) {
            if (!options.icon)
                options.icon = options.type;
            delete options.type;
        }

        return window.Swal.fire(options);
    };
    $.admin.toastr = toastr;
    $.admin.grid = new Grid();

    $.admin.reload = function () {
        $.pjax.reload('#pjax-container');
        $.admin.grid = new Grid();
    };

    $.admin.redirect = function (url) {
        $.pjax({container:'#pjax-container', url: url });
        $.admin.grid = new Grid();
    };

    $.admin.getToken = function () {
        return $('meta[name="csrf-token"]').attr('content');
    };

    $.admin.loadedScripts = [];

    $.admin.loadScripts = function(arr) {
        var _arr = $.map(arr, function(src) {

            if ($.inArray(src, $.admin.loadedScripts)) {
                return;
            }

            $.admin.loadedScripts.push(src);

            return $.getScript(src);
        });

        _arr.push($.Deferred(function(deferred){
            $(deferred.resolve);
        }));

        return $.when.apply($, _arr);
    }

    function initTooltips() {
        $('[data-toggle="tooltip"]').tooltip();
    }

    $(function () {
        initTooltips();
    });

    $(document).on('pjax:end', function () {
        initTooltips();
    });

    $(document).on('click', '.column-selector .dropdown-menu', function (e) {
        e.stopPropagation();
    });

    // select2 search autofocus fix for jQuery 3.6
    $(document).on('select2:open', function (e) {
        const s2 = $(e.target).data('select2');
        if (!s2) return;

        const $dropdownField = s2.$dropdown && s2.$dropdown.find('.select2-search__field');
        if ($dropdownField && $dropdownField.length) {
            $dropdownField[0].focus();
            return;
        }

        const $inlineField = s2.$selection && s2.$selection.find('.select2-search__field');
        if ($inlineField && $inlineField.length) {
            $inlineField[0].focus();
        }
    });
})(jQuery);
