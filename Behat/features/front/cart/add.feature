@product @cart
Feature: Add product to cart
    In order to sell product
    As a customer
    I need to be able to add product to my cart

    Background:
        Given I am logged in as an administrator
        And The following brands:
            | name |
            | Acme |
        And The following categories:
            | name |
            | Dummies |

    Scenario: Add a simple product to the cart
        Given The following products:
            | type | designation | reference | netPrice | weight |
            | simple | Dummy A   | DUM-001   | 33.325   | 0.150  |
        When I go to "ekyna_product_front_product_detail" route with "productId:1"
        And I press "sale_item_configure[submit]"
        Then I should see "Cet article a bien été ajouté à votre panier"

    # TODO variable

    # TODO bundle

    # TODO configurable

    # TODO pricing

    # TODO filtering
