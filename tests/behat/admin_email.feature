@block @block_quickmail
Feature: Verify Admin behavior for Quickmail
  In order to communicate with classes of Moodle users
    As an admin
    I need the ability to email arbitrary  groups of teachers, students, etc.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@asd.com |
      | student1 | Student | 1 | student1@asd.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log in as "admin"
    And I am on homepage
    When I follow "Turn editing on"
    And I add the "Quickmail" block
    Then I should see "Send Admin Email" in the "Quickmail" "block"
    And I am on homepage
    And I follow "Send Admin Email"
    Then I should see "Send Admin Email"

Scenario: Send email to one user
    Given I set the field "courserole_rl" to "Student"
    And I press "Add filter"
    Then I should see "student1"
    And I set the following fields to these values:
        |Subject |test_subject |
        |Body |test_body |
    And I press "Send Email"
    Then I should see "all messages sent successfully"

Scenario: Send email to multiple users
    Given I set the following fields to these values:
        | username | t |
    And I press "Add filter"
    Then I should see "student1"
    And  I should see "teacher1"
    And I set the following fields to these values:
        |Subject |test_subject |
        |Body |test_body |
    And I press "Send Email"
    Then I should see "all messages sent successfully"

Scenario: Ensure user filter persists in SESSION after navigating away
    Given I set the field "courserole_rl" to "Student"
    And I press "Add filter"
    Then I should see "student1"
    And  I follow "Home"
    And I follow "Send Admin Email"
    Then I should see "student1"

Scenario: Ensure form re-populates when loading a previously sent message.
    Given I set the following fields to these values:
        | username | t |
    And I press "Add filter"
    Then I should see "student1"
    And  I should see "teacher1"
    And I set the following fields to these values:
        |Subject |test_subject |
        |Body |test_body |
    And I press "Send Email"
    Then I should see "all messages sent successfully"
    And I follow "Home"
    And I follow "Send Admin Email"
    Then I should not see "student1"
    And  I should not see "teacher1"
    And I follow "Home"
    And I follow "View History"
    And I follow "Open Email"
    Then I should see "student1"
    And  I should see "teacher1"

