@product @attribute-group
Feature: Remove attribute groups
    In order to manage attributes for product variants
    As an administrator
    I need to be able to remove attribute groups

    Background:
        Given I am logged in as an administrator
        And The following attribute groups:
            | name    |
            | Couleur |

    Scenario: Remove an attribute group
        When I go to "ekyna_product_attribute_admin_remove" route with "attributeGroupId:1"
        And I check "form[confirm]"
        And I press "form[actions][remove]"
        Then I should see the resource removed confirmation message
        And I should not see "Couleur"

    # TODO Scenario: Remove an attribute group used by a product
    # TODO Scenario: Remove an attribute group used by an attribute set
