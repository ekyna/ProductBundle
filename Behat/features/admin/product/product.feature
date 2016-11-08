Feature: Adding new products
    In order to extend my catalog
    As an administrator
    I need to be able to create products

    Background:
        Given I am logged in as an administrator

    Scenario: I can see the list of products
        When I go to "ekyna_product_product_admin_list" route
        Then I should see "Liste des produits"
