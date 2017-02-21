Feature: Create attribute groups
    In order to manage attributes for product variants
    As an administrator
    I need to be able to new attribute groups

    Background:
        Given I am logged in as an administrator

    Scenario: Create an attribute group
        When I go to "ekyna_product_attribute_group_admin_new" route
        And I fill in "attribute_group[name]" with "Couleur"
        And I fill in "attribute_group[translations][fr][title]" with "Couleur"
        And I press "attribute_group_actions_save"
        Then I should see the resource saved confirmation message
        And I should see "Couleur"
