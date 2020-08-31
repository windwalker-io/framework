# Replace 

## Headers

```regexp
\<\?php (declare.+;)\n(/\*\*[\w\d\W\s]+?\*\/)
```
TO

```regexp
\<\?php\n\n$2\n\n$1
```
