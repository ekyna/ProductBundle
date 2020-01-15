@product @cart
Feature: Add products to cart
    In order to buy product
    As a customer
    I need to be able to add products to my cart

    Scenario: Add a simple product to the cart
        Given My cart is empty

        When I go to "ekyna_product_front_product_detail" route with "reference:TABA"
        And I wait until the add to cart form is ready
        And I press "sale_item_configure_submit"
        Then I should see "The product Samsung Tablet A has been added to your cart."

        When I go to "ekyna_commerce_cart_checkout_index" route
        Then I should see "TABA" in the "#item_0_reference" element
        And I should see "€240.00" in the "#item_0_base" element
        And I should not see an "#item_1_reference" element

    Scenario: Add a variable product to the cart
        Given My cart is empty

        When I go to "ekyna_product_front_product_detail" route with "reference:TABC"
        And I wait until the add to cart form is ready
        And I press "sale_item_configure_submit"
        Then I should see "The product Apple Tablet C WiFi 16 Go White has been added to your cart."

        When I go to "ekyna_commerce_cart_checkout_index" route
        Then I should see "TABC" in the "#item_0_reference" element
        And I should see "€320.00" in the "#item_0_base" element
        And I should not see an "#item_1_reference" element

    Scenario: Add a variable product to the cart by picking a variant and an option
        Given My cart is empty

        When I go to "ekyna_product_front_product_detail" route with "reference:SHB"
        And I wait until the add to cart form is ready
        Then I should see "Out of stock"

        When I select "9 in Black" from "sale_item_configure_variant"
        And I select "Rotate Cuff Black" from "sale_item_configure[options][option_group_4][choice]"
        Then I should not see "Out of stock"
        And I should see "€80.00" in the "#sale_item_configure_pricing" element

        When I press "sale_item_configure_submit"
        Then I should see "The product Acme Kiosk Shell B 9 in Black has been added to your cart."

        When I go to "ekyna_commerce_cart_checkout_index" route
        Then I should see "SHB-B" in the "#item_0_reference" element
        And I should see "€70" in the "#item_0_base" element
        And I should see "ROC-B" in the "#item_1_reference" element
        And I should see "€10.00" in the "#item_1_base" element
        And I should not see an "#item_2_reference" element

    Scenario: Add a bundle product to the cart
        Given My cart is empty

        When I go to "ekyna_product_front_product_detail" route with "reference:NRKE"
        And I wait until the add to cart form is ready
        And I select "Kiosk Extension Shell White" from "sale_item_configure[options][option_group_1][choice]"
        And I select "Type-C Connector" from "sale_item_configure[options][option_group_5][choice]"
        And I should see "130.00" in the "#sale_item_configure_pricing" element

        When I press "sale_item_configure_submit"
        Then I should see "The product Acme NFC Reader Kiosk Extension has been added to your cart."

        When I go to "ekyna_commerce_cart_checkout_index" route
        Then I should see "NRKE" in the "#item_0_reference" element
        And I should see "€20.00" in the "#item_0_base" element
        And I should see "HUB" in the "#item_1_reference" element
        And I should see "€70.00" in the "#item_1_base" element
        And I should see "KES-W" in the "#item_2_reference" element
        And I should see "€40.00" in the "#item_2_base" element
        And I should not see an "#item_3_reference" element

    Scenario: Add a configurable product to the cart
        Given My cart is empty

        When I go to "ekyna_product_front_product_detail" route with "reference:KICO"
        And I wait until the add to cart form is ready
        And I select the choice "2" from the slot "1"
        And I select "9 in Black" from "sale_item_configure[configuration][slot_3][variant]"
        And I select "Rotate Cuff Black" from "sale_item_configure[configuration][slot_3][options][option_group_4][choice]"
        And I select the choice "2" from the slot "2"
        And I select "Black" from "sale_item_configure[configuration][slot_4][variant]"
        And I select the choice "1" from the slot "3"
        And I select "Kiosk Extension Shell White" from "sale_item_configure[configuration][slot_5][options][option_group_1][choice]"
        And I select "Type-C Connector" from "sale_item_configure[configuration][slot_5][options][option_group_5][choice]"
        And I select "Hand work" from "sale_item_configure[options][option_group_2][choice]"
        Then I should see "300.00" in the "#sale_item_configure_pricing" element

        When I press "sale_item_configure_submit"
        Then I should see "The product Acme Kiosk configurator has been added to your cart."

        When I go to "ekyna_commerce_cart_checkout_index" route
        Then I should see "Kiosk configurator" in the "#item_0_designation" element
        And I should see "KICO" in the "#item_1_reference" element
        And I should see "€30.00" in the "#item_1_base" element
        And I should see "SHB-B" in the "#item_2_reference" element
        And I should see "€70.00" in the "#item_2_base" element
        And I should see "ROC-B" in the "#item_3_reference" element
        And I should see "€10.00" in the "#item_3_base" element
        And I should see "KSB-B" in the "#item_4_reference" element
        And I should see "€60.00" in the "#item_4_base" element
        And I should see "HUB" in the "#item_5_reference" element
        And I should see "€70.00" in the "#item_5_base" element
        And I should see "KES-B" in the "#item_6_reference" element
        And I should see "€40.00" in the "#item_6_base" element
        And I should not see an "#item_7_reference" element

    # TODO pricing

    # TODO filtering
