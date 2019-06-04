@product @cart
Feature: Add products to cart
    In order to buy product
    As a customer
    I need to be able to add products to my cart

    Scenario: Add a simple product to the cart
        Given My cart is empty

        When I go to "ekyna_product_front_product_detail" route with "productId:7"
        And I wait until the add to cart form is ready
        And I press "sale_item_configure_submit"
        Then I should see "L'article Tablette Store Coque VESA 11 à 13 pouces a bien été ajouté à votre panier."

        When I go to "ekyna_commerce_cart_checkout_index" route
        Then I should see "COQU-VESA-13" in the "#item_0_reference" element
        And I should see "60,00 €" in the "#item_0_base" element
        And I should not see an "#item_1_reference" element

    Scenario: Add a variable product to the cart
        Given My cart is empty

        When I go to "ekyna_product_front_product_detail" route with "productId:1"
        And I wait until the add to cart form is ready
        And I press "sale_item_configure_submit"
        Then I should see "L'article Samsung Galaxy Tab A 9.7 WiFi 16 Go Blanc a bien été ajouté à votre panier."

        When I go to "ekyna_commerce_cart_checkout_index" route
        Then I should see "GALA-TABA-1" in the "#item_0_reference" element
        And I should see "320,00 €" in the "#item_0_base" element
        And I should not see an "#item_1_reference" element

    Scenario: Add a variable product to the cart by picking a variant and an option
        Given My cart is empty

        When I go to "ekyna_product_front_product_detail" route with "productId:1"
        And I wait until the add to cart form is ready
        And I select "WiFi 3G 32 Go Blanc" from "sale_item_configure_variant"
        And I select "2 ans" from "sale_item_configure[options][option_group_2][choice]"
        And I press "sale_item_configure_submit"
        Then I should see "L'article Samsung Galaxy Tab A 9.7 WiFi 3G 32 Go Blanc a bien été ajouté à votre panier."

        When I go to "ekyna_commerce_cart_checkout_index" route
        Then I should see "GALA-TABA-3" in the "#item_0_reference" element
        And I should see "340,00 €" in the "#item_0_base" element
        And I should see "ABON-1YEA" in the "#item_1_reference" element
        And I should see "35,00 €" in the "#item_1_base" element
        And I should see "WARA-2YEA" in the "#item_2_reference" element
        And I should see "60,00 €" in the "#item_2_base" element
        And I should not see an "#item_3_reference" element

    Scenario: Add a bundle product to the cart
        Given My cart is empty

        When I go to "ekyna_product_front_product_detail" route with "productId:13"
        And I wait until the add to cart form is ready
        And I select "Manchon Rotate Pied de Borne" from "sale_item_configure[options][option_group_3][choice]"
        And I select "Montage dans nos ateliers" from "sale_item_configure[options][option_group_5][choice]"
        And I press "sale_item_configure_submit"
        Then I should see "L'article Tablette Store Pack borne 1 a bien été ajouté à votre panier."

        When I go to "ekyna_commerce_cart_checkout_index" route
        Then I should see "BUND-BORN-1" in the "#item_0_reference" element
        And I should see "5,00 €" in the "#item_0_base" element
        And I should see "COQU-VESA-11" in the "#item_1_reference" element
        And I should see "50,00 €" in the "#item_1_base" element
        And I should see "MANC-ROTA" in the "#item_2_reference" element
        And I should see "10,00 €" in the "#item_2_base" element
        And I should see "SECU-CABL-KEY" in the "#item_3_reference" element
        And I should see "10,00 €" in the "#item_3_base" element
        And I should see "MONTAGE" in the "#item_4_reference" element
        And I should see "10,00 €" in the "#item_4_base" element
        And I should see "MANC-SWIT" in the "#item_5_reference" element
        And I should see "20,00 €" in the "#item_5_base" element
        And I should see "SOCL-COUN" in the "#item_6_reference" element
        And I should see "60,00 €" in the "#item_6_base" element
        And I should not see an "#item_7_reference" element

    @current
    Scenario: Add a configurable product to the cart
        Given My cart is empty

        When I go to "ekyna_product_front_product_detail" route with "productId:14"
        And I wait until the add to cart form is ready
        And I select the choice "2" from the slot "1"
        And I select the choice "2" from the slot "2"
        And I select "WiFi 3G 32 Go Blanc" from "sale_item_configure[configuration][slot_8][variant]"
        And I select "2 ans" from "sale_item_configure[configuration][slot_8][options][option_group_2][choice]"
        And I press "sale_item_configure_submit"
        Then I should see "L'article Tablette Store Configurable borne 1 a bien été ajouté à votre panier."

        When I go to "ekyna_commerce_cart_checkout_index" route
        Then I should see "Configurable borne 1" in the "#item_0_designation" element
        And I should see "COQU-VESA-13" in the "#item_1_reference" element
        And I should see "60,00 €" in the "#item_1_base" element
        And I should see "MANC-ROTA" in the "#item_2_reference" element
        And I should see "20,00 €" in the "#item_2_base" element
        And I should see "SOCL-COUN" in the "#item_3_reference" element
        And I should see "60,00 €" in the "#item_3_base" element
        And I should see "GALA-TABA-3" in the "#item_4_reference" element
        And I should see "340,00 €" in the "#item_4_base" element
        And I should see "ABON-1YEA" in the "#item_5_reference" element
        And I should see "35,00 €" in the "#item_5_base" element
        And I should see "WARA-2YEA" in the "#item_6_reference" element
        And I should see "60,00 €" in the "#item_6_base" element
        And I should not see an "#item_7_reference" element

    # TODO pricing

    # TODO filtering
