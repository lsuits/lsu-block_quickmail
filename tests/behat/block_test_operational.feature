@block @quickmail @rl
Feature: Test the complete sequence of operational steps for a block
  In order to guarantee block functionality
  As an administrator
  I need to be able to add an instance of the block to a course
  And backup/restore the course to verify the block instance persists
  And delete an instance of the block within a course
  And delete a course containing an instance of the block

  @javascript
  Scenario: Testing the complete sequence of operational steps for an activity module
    When I log in as "admin"
    And I create a course with:
      | Course full name | Test Course |
      | Course short name | testcourse |
    And I follow "Test Course"
    And I turn editing mode on
    And I add the "Quickmail" block
    Then I should see "Compose New Email" in the "Quickmail" "block"
    When I backup "Test Course" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Test Restored Course |
    Then I should see "Compose New Email" in the "Quickmail" "block"
    When I open the "Quickmail" blocks action menu
    And I click on "Delete" "link" in the "Quickmail" "block"
    And I press "Yes"
    And I turn editing mode off
    Then I should not see "Quickmail"
    When I go to the courses management page
    And I click on "delete" action for "Test Course" in management course listing
    And I should see "Delete testcourse"
    And I press "Delete"
    And I should see "testcourse has been completely deleted"
    And I press "Continue"
    Then I should not see "Test Course" in the "#course-category-listings ul.ml" "css_element"
