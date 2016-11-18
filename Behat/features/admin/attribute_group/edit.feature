Feature: Edit attribute groups
    In order to manage attributes for product variants
    As an administrator
    I need to be able to edit attribute groups

    Background:
        Given I am logged in as an administrator
        And The following attribute groups:
            | name    |
            | Couleur |

    Scenario: Edit the attribute group
        When I go to "ekyna_product_attribute_group_admin_edit" route with "attributeGroupId:1"
        And I fill in "attribute_group[name]" with "Taille"
        And I fill in "attribute_group[translations][fr][title]" with "Taille"
        And I press "attribute_group_actions_save"
        Then I should see "La ressource a été sauvegardée avec succès"
        And I should see "Taille"
