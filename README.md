# Quickmail

Quickmail is a Moodle block that provides selective, bulk emailing within courses.

## Features

* Multiple attachments
* Drafts
* Signatures
* Filter by Role
* Filter by Groups
* Optionally allow Students to email people within their group.
* Alternate sending email
* Embed images and other content in emails and signatures

## Multiple Attachments

Quickmail supports multiple attachments by zipping up a Moodle filearea, and
sending it along to `email_to_user`.

1. Quickmail assumes that `$CFG->tempdir` is in `$CFG->dataroot`. This
limitation exists because Quickmail uses `email_to_user`.
2. Make sure your email service supports zip's, otherwise they will be filtered.

__Note__: There are future plans to have Quickmail piggyback on 2.x internal
messaging, which will provide a lot of implicit benefits as well as the
negation of sending archives as attachments.

## Download

Visit [Quickmail's Github page][quickmail_github] to either download a package or clone the git repository.

## Installation

Quickmail should be installed like any other block. See [the Moodle Docs page on block installation][block_doc].

## Contributions

Contributions of any form are welcome. Github pull requests are preferred.

File any bugs, improvements, or feature requiests in our [issue tracker][issues].

## License

Quickmail adopts the same license that Moodle does.

## Screenshots

![Block][block]

---

![Email][email]

---

![Signatures][signature]

---

![Configuration][config]

[quickmail_github]: https://github.com/lsuits/quickmail
[block_doc]: http://docs.moodle.org/20/en/Installing_contributed_modules_or_plugins#Block_installation
[block]: https://tigerbytes2.lsu.edu/users/pcali1/work/block.png
[config]: https://tigerbytes2.lsu.edu/users/pcali1/work/config.png
[signature]: https://tigerbytes2.lsu.edu/users/pcali1/work/signature.png
[email]: https://tigerbytes2.lsu.edu/users/pcali1/work/email.png
[issues]: https://github.com/lsuits/quickmail/issues
