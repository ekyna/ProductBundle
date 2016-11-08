Feature: Create attributes
    In order to manage attributes for product variants
    As an administrator
    I need to be able to new attributes

    Background:
        Given I am logged in as an administrator
        And The following attribute groups:
            | name    |
            | Couleur |

    Scenario: Create an attribute
        When I go to "ekyna_product_attribute_admin_new" route with "attributeGroupId:1"
        And I fill in "attribute[name]" with "Blanc"
        And I select "Couleur" from "attribute[group]"
        And I press "attribute_actions_save"
        Then I should see "La ressource a été sauvegardée avec succès"
        And I should see "Blanc"
