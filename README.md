# Helper

Conjunto de classes auxiliadoras para Conexão em Banco (Conn), Controle de recebimento de requisições (Uri), Execução de
requisições (HttpRequest), etc...

## Adicionando ao projeto

```shell
composer require Szagot/Helper
```

## Detalhamento de cada Helper

### Conexão ao Banco: `Szagot\Helper\Conn`

Conectando ao banco

```php
$conn = new Connection(
    'banco',
    'localhost',
    'root',
    'senha'
);
```

Preparando base para execuções na conexão

```php
Query::setConn($conn);
```

Fazendo uma consulta diretamente

```php
$search = Query::exec(
    'SELECT * FROM tabela WHERE name LIKE :name',
    [
        'name' => "%{$name}%",
    ],
    MinhaClassePersonalizada::class
)
```

Fazendo a mesma requisição acima Crud

```php
try {
    $search = Crud::search(MinhaClassePersonalizada::class, 'name', "%{$name}%");
} catch (ConnException $e) {
    exit($e);
}
```

Fazendo uma inserção direta

```php
Query::exec(
    'INSERT INTO tabela (campo1, campo2) VALUES (:campo1, :campo2)',
    [
        'campo1' => $valor1,
        'campo2' => $valor2,
    ],
    MinhaClassePersonalizada::class
)

$id = Query::getLastLog()?->getLastId() ?? null;
```

Fazendo uma inserção com Crud

```php
try {
    $id = Crud::insert(MinhaClassePersonalizada::class, 'id', $minhaInstancia);
} catch (ConnException $e) {
    exit($e);
}
```

#### Utilizando o Crud

Para utilizar o Crud de modo correto, é necessário:

* Criar uma conexão do tipo Connection
* Adicionar ela na Query
* Ter models que tenham sido estendidos de aModel

Exemplo básico:

```php
// Model
class MinhaClassePersonalizada extends \Szagot\Helper\Conn\aModel
{
    const TABLE = 'nome_da_tabela_do_banco';
    
    private int     $id;
    private ?string $campo1;
    private ?string $campo2;
}

// Preparando conexão
Query::setConn(
    new Connection(
        'banco',
        'localhost',
        'root',
        ''
    )
);

// Pegando um registro específico:
try {
    /** @var MinhaClassePersonalizada $minhaInstancia */
    $minhaInstancia = Crud::get(MinhaClassePersonalizada::class, 'id', 1);
} catch (ConnException $e) {
    exit($e);
}
```

---

### Controle de Recebimento Requisições: `Szagot\Helper\Uri`

Em breve....

---

### Execução de Requisições: `Szagot\Helper\HttpRequest`

Em breve....