@block @block_quickmail @WIP @javascript
Feature: Verify course-level behavior of Quickmail
  In order to communicate with members of my course
    As an instructor
    I need the ability to use and configure Quickmail on a per-course basis.

Background:
    Given the following "courses" exist:
        | fullname | shortname | category | groupmode |
        | Course 1 | C1 | 0 | 1 |
    And the following "users" exist:
        | username | firstname | lastname | email |
        | teacher1 | Teacher | 1 | teacher1@asd.com |
        | student1 | Student | 1 | student1@asd.com |
        | student2 | Student | 2 | student2@asd.com |
    And the following "course enrolments" exist:
        | user | course | role |
        | teacher1 | C1 | editingteacher |
        | student1 | C1 | student |
        | student2 | C1 | student |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    When I add the "Quickmail" block
    Then I should see "Compose New Email" in the "Quickmail" "block"

Scenario: Test that form re-populates correctly.
    Given I click on "Compose New Email" "link" in the "Quickmail" "block"
    And I set the following fields to these values:
        | from_users | Student 1, Student 2|
    And I press "Add"
    And I set the following fields to these values:
        | Additional Emails | fake1@example.com;fake2@example.com, fake3@example.com |
        | Subject           | test subject      |
        | Message           | test message body |
    When I press "Send Email"
    Then I should see "all messages sent successfully"
    And I follow "Open Email"
    Then I should see "Student 1" in the "#mail_users" "css_element"
    And I should see "Student 2" in the "#mail_users" "css_element"
    And the field "Additional Emails" matches value "fake1@example.com;fake2@example.com, fake3@example.com"

Scenario: Test that email sends to selected students.
    Given I click on "Compose New Email" "link" in the "Quickmail" "block"
    And I set the following fields to these values:
        | from_users | Student 1, Student 2|
    And I press "Add"
    And I set the following fields to these values:
        | Subject           | test subject      |
        | Message           | test message body |
    When I press "Send Email"
    Then I should see "all messages sent successfully"

Scenario: Test that email sends with 'additional emails' only.
    Given I click on "Compose New Email" "link" in the "Quickmail" "block"
    And I set the following fields to these values:
        | Additional Emails | fake1@example.com;fake2@example.com, fake3@example.com |
        | Subject           | test subject      |
        | Message           | test message body |
    When I press "Send Email"
    Then I should see "all messages sent successfully"

Scenario: As an Instructor, configure Quickmail to allow/disallow student use.
    Given I log out
    And I log in as "student1"
    When I follow "Course 1"
    Then I should not see "Quickmail"
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I click on "Configuration" "link" in the "Quickmail" "block"
    When  I set the field "Allow students to use Quickmail" to "Yes"
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    Then I should see "Compose New Email" in the "Quickmail" "block"
    And I click on "Compose New Email" "link" in the "Quickmail" "block"
    Then I should see "Potential Recipients"

Scenario: Setup signature.
    Given I click on "Signatures" "link" in the "Quickmail" "block"
    And I set the following fields to these values:
        | Title     | Test Sig  |
        | Signature | this is my sig    |
    And I press "Save changes"
    And I follow "C1"
    When I follow "Compose New Email"
    Then the "Signatures" select box should contain "Test Sig" 