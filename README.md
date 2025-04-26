# dmarc-report-parser

A PHP based tool to parse DMARC reports from an IMAP mailbox or from the filesystem, and insert the information into a
database. Based on [techsneeze/dmarcts-report-parser](https://github.com/techsneeze/dmarcts-report-parser)

The imported DMARC reports can be viewed
with: [techsneeze/dmarcts-report-viewer](https://github.com/techsneeze/dmarcts-report-viewer)


## Installation

```bash
cp .env.local.dict .env.local
```

Adapt .env.local to your needs.

### Test your config

Run

```bash
app:system:check
```

to see, if your configuration works.

### Crate or Update Database tables:

To set up or upgrade your database run the following command to see the SQL queries, that will be executed.
Verify you are using the correct database!

```bash
php bin/console doctrine:schema:update --dump-sql
```

If everything looks fine, then you can run the update.

```bash
php bin/console doctrine:schema:update --force
```

## Import mails

To import the mails just run.

```bash
php bin/console import:imap
```

Now you can view them in dmarcts-report-viewer or any compatible app.
