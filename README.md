mental-note
===========

**work in progress**

Little web application to collect your web findings. Minimal interface.

- **categorize** what is the link intended for: read, watch, visit regularly, ...
- **tag** make your links easy to filter
- **search** simple and fast
- **mark as done** collecting lots of article links, you read once? mark them as read and be done with it
- **multi-user**
- **automatic thumbnail generation** if no thumbnail can be calculated, uses cutycapt to take a snapshot of the page

obligatory screenshot

![](docs/main-interface.png)

switch to doctrine migrations
-----------------------------

if your dev installation is older than 2015-05-15 you will need to add the initial migration manually

```
php app/console do:mi:version --add 20150515104913
```

