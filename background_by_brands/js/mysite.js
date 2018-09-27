path_shop = location.pathname;
shop      = path_shop.match(/^\/shop|\/loja/);
path      = location.search;

// var brands  = ["adidas", "asics"]; percorrer array
// jQuery.each(brands, function (index, value) {
//     a = '/='+value+'/';
//     b = path.match(a);
//     console.log(b, a);
// });
// woof_init_search_form();
// Ao clicar no reset
jQuery(document).ready( function($) {
    jQuery( ".woof_reset_search_form" ).click(function() {
        console.log(234);
        background_color                 = "#f2f2f2";
        color_text                       = "#323232";
        background_color_section_1       = "#101415";
        background_color_section_2       = "#1c2d30";
        background_color_search          = "#283e42";
        background_color_cart            = "#283e42";
        img_brand                        = null;

        jQuery(".site-title").css({background: background_color }); // Faixa debaixo do menu
        jQuery(".site-title h1").css({color: color_text }); // Texto da faixa
        jQuery(".section-1").css({background: background_color_section_1 }); // Header 1
        jQuery(".section-2").css({background: background_color_section_2 }); // Header 2
        jQuery(".hb-s2i5").css({background: background_color_search }); // Caixa de pesquisa
        jQuery(".hb-s2i4").css({background: background_color_cart }); // Carrinho
        /*if (img_brand != null) {
            if(jQuery( "div" ).hasClass( "div_img_brand" )){
                jQuery("#the_brand_img").attr("src","https://www.andebol7.pt/wp-content/uploads/2018/03/"+img_brand+".jpg");
            }
            else {
                jQuery(".title-desc" ).append( "<div class='div_img_brand'><div>" ); // woocommerce-products-header
                jQuery('.div_img_brand').prepend('<img id="the_brand_img" src="https://www.andebol7.pt/wp-content/uploads/2018/03/'+img_brand+'.jpg" />');
            }
            jQuery("#the_brand_img").css({"width": "75px", "height": "42.5px"});
        }
        else {
            jQuery( ".div_img_brand" ).remove();
        }*/
    });
});

// Se estiver em loja
if (shop != null) {
    brand = path.match(/product_tag=\w+/); // \product_tag=\w+& brand = path.match(/=\w+$/);

    if (brand != null) {
        var background_color;

        moreThanOne = path.match(/product_tag=\w+(,|%)/);
        brand = (moreThanOne != null ? moreThanOne : brand[0])

        jQuery(document).ready( function($) {
            switch (brand) {
                case 'product_tag=adidas':
                    background_color                 = "#000";
                    color_text                       = "#fff";
                    background_color_section_1       = "#000";
                    background_color_section_2       = "#000";
                    background_color_search          = "#000";
                    background_color_cart            = "#000";
                    img_brand                        = 'adidas';
                    break;

                case 'product_tag=asics':
                    background_color                 = "#001e62";
                    color_text                       = "#00bbdc";
                    background_color_section_1       = "#00bbdc";
                    background_color_section_2       = "#00bbdc";
                    background_color_search          = "#001e62";
                    background_color_cart            = "#001e62";
                    img_brand                        = 'asics';
                    break;

                case 'product_tag=mizuno':
                    background_color                 = "#08176d";
                    color_text                       = "#fff";
                    background_color_section_1       = "#08176d";
                    background_color_section_2       = "#08176d";
                    background_color_search          = "#08176d";
                    background_color_cart            = "#08176d";
                    img_brand                        = 'mizuno';
                    break;

                case 'product_tag=salming':
                    background_color                 = "#009fe3";
                    color_text                       = "#fff";
                    background_color_section_1       = "#000";
                    background_color_section_2       = "#000";
                    background_color_search          = "#000";
                    background_color_cart            = "#000";
                    img_brand                        = 'salming';
                    break;

                // Cores default
                default:
                    background_color                 = "#f2f2f2";
                    color_text                       = "#323232";
                    background_color_section_1       = "#101415";
                    background_color_section_2       = "#1c2d30";
                    background_color_search          = "#283e42";
                    background_color_cart            = "#283e42";
                    img_brand                        = null;
                    break;
            }

            jQuery(".site-title").css({background: background_color }); // Faixa debaixo do menu
            jQuery(".site-title h1").css({color: color_text }); // Texto da faixa
            jQuery(".section-1").css({background: background_color_section_1 }); // Header 1
            jQuery(".section-2").css({background: background_color_section_2 }); // Header 2
            jQuery(".hb-s2i5").css({background: background_color_search }); // Caixa de pesquisa
            jQuery(".hb-s2i4").css({background: background_color_cart }); // Carrinho
            /*if (img_brand != null) {
                if(jQuery( "div" ).hasClass( "div_img_brand" )){
                    jQuery("#the_brand_img").attr("src","https://www.andebol7.pt/wp-content/uploads/2018/03/"+img_brand+".jpg");
                }
                else {
                    jQuery(".title-desc" ).append( "<div class='div_img_brand'><div>" ); // woocommerce-products-header
                    jQuery('.div_img_brand').prepend('<img id="the_brand_img" src="https://www.andebol7.pt/wp-content/uploads/2018/03/'+img_brand+'.jpg" />');
                }
                jQuery("#the_brand_img").css({"width": "75px", "height": "42.5px"});
            }
            else {
                jQuery( ".div_img_brand" ).remove();
            }*/
            if (img_brand != null) {
                jQuery(".content-logo a img" ).attr("src","https://www.andebol7.pt/wp-content/uploads/2018/06/logo-"+img_brand+".png");
            }
            else {
                jQuery(".content-logo a img" ).attr("src","https://www.andebol7.pt/wp-content/uploads/2018/01/logo.png");
            }
        });
    }
}

// jQuery(".content-logo a img" ).attr("src","https://www.andebol7.pt/wp-content/uploads/2018/05/logo-adidas.png");

/*plugins/woocmmerce-product-filter/html_types/checkbox.js
Colorcar no final do woof_checkbox_direct_search ->>> change_background_color(woof_current_values.product_tag); <<<-

// Change background when select a brand
function change_background_color(name) {
    switch (name) {
        case 'adidas':
            background_color                 = "#000";
            color_text                       = "#fff";
            background_color_section_1       = "#000";
            background_color_section_2       = "#000";
            background_color_search          = "#000";
            background_color_cart            = "#000";
            img_brand                        = 'adidas';
            break;

        case 'asics':
            background_color                 = "#001e62";
            color_text                       = "#00bbdc";
            background_color_section_1       = "#00bbdc";
            background_color_section_2       = "#00bbdc";
            background_color_search          = "#001e62";
            background_color_cart            = "#001e62";
            img_brand                        = 'asics';
            break;

        case 'mizuno':
            background_color                 = "#08176d";
            color_text                       = "#fff";
            background_color_section_1       = "#08176d";
            background_color_section_2       = "#08176d";
            background_color_search          = "#08176d";
            background_color_cart            = "#08176d";
            img_brand                        = 'mizuno';
            break;

        case 'salming':
            background_color                 = "#009fe3";
            color_text                       = "#fff";
            background_color_section_1       = "#000";
            background_color_section_2       = "#000";
            background_color_search          = "#000";
            background_color_cart            = "#000";
            img_brand                        = 'salming';
            break;

        // Cores default
        default:
            background_color                 = "#f2f2f2";
            color_text                       = "#323232";
            background_color_section_1       = "#101415";
            background_color_section_2       = "#1c2d30";
            background_color_search          = "#283e42";
            background_color_cart            = "#283e42";
            img_brand                        = null;
            break;
    }

    jQuery(".site-title").css({background: background_color }); // Faixa debaixo do menu
    jQuery(".site-title h1").css({color: color_text }); // Texto da faixa
    jQuery(".section-1").css({background: background_color_section_1 }); // Header 1
    jQuery(".section-2").css({background: background_color_section_2 }); // Header 2
    jQuery(".hb-s2i5").css({background: background_color_search }); // Caixa de pesquisa
    jQuery(".hb-s2i4").css({background: background_color_cart }); // Carrinho

    if (img_brand != null) {
        jQuery(".content-logo a img" ).attr("src","https://www.andebol7.pt/wp-content/uploads/2018/06/logo-"+img_brand+".png");
    }
    else {
        jQuery(".content-logo a img" ).attr("src","https://www.andebol7.pt/wp-content/uploads/2018/01/logo.png");
    }
}*/

/* plugins/woocommerce-products-filter/js/front.js
Dentro de: jQuery('.woof_remove_ppi').parent().click(function ()
l. 781
    c = 0, help = "";
l.789 (dentro do if)
    c++;
    help = value;
l.796
    if(tax != 'product_cat'){(c == 1 ? change_background_color(help) : change_background_color(null));}
*/

/*Reset button:
Dentro do click 'woof_reset_search_form' no ficheiro front.js
change_background_color(null);*/
