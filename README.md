<h1 align="center">Flysystem Github</h1>

# Requirement

- PHP >= 7.2

# install

~~~
composer require pkg6/flysystem-github
~~~

# CDN List

~~~
'github'   => "https://raw.githubusercontent.com/:username/:repository/:branch/:fullfilename",
'fastgit'  => "https://hub.fastgit.org/:username/:repository/blob/:branch/:fullfilename?raw=true",
'jsdelivr' => "https://cdn.jsdelivr.net/gh/:username/:repository@:branch/:fullfilename"
~~~


# Usage

```php
use pkg6\flysystem\github\GithubAdapter;
use League\Flysystem\Filesystem;

$token='xxxxxx';
$username='xxxxxx';
$repository='test';
$adapter = new GithubAdapter($token,$username,$repository);
$flysystem = new League\Flysystem\Filesystem($adapter);

```

## API

```php
false|array $flysystem->write('file.md', 'contents');

false|array $flysystem->writeStream('file.md', fopen('path/to/your/local/file.jpg', 'r'));

false|array $flysystem->update('file.md', 'new contents');

false|array $flysystem->updateStream('file.md', fopen('path/to/your/local/file.jpg', 'r'));

false|array $flysystem->delete('file.md');

bool $flysystem->has('file.md');

bool $flysystem->rename('file.md','newfile.md');

bool $flysystem->copy('file.md','newfile.md');

false|array $flysystem->read('file.md');

string|false $flysystem->readStream('file.md');

array $flysystem->listContents();

string|false $flysystem->getMetadata('file.md');

int $flysystem->getSize('file.md');

string $flysystem->getAdapter()->getUrl('file.md'); 

string|false $flysystem->getMimetype('file.md');
```

## Plugins

File Url:

```
use pkg6\flysystem\github\Plugins\FileUrl;
$flysystem->addPlugin(new FileUrl());
string $flysystem->getUrl('file.md');
```

