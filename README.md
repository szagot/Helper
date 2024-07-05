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
    $id = Crud::insert(MinhaClassePersonalizada::class, $minhaInstancia);
} catch (ConnException $e) {
    exit($e);
}
```

#### Utilizando o Crud

Para utilizar o Crud de modo correto, é necessário:

* Criar uma conexão do tipo Connection
* Adicionar ela na Query
* Ter models que tenham sido estendidos de aModel
* Usar os attributes obrigatórios Table e PrimaryKey
* Se um campo do seu model for extra, isto é, não tiver um campo de mesmo nome na tabela, use o atributo opcional
  IgnoreField

> **Obs**.: No caso de tabelas personalizadas que não possuam primary Key, utilize Query diretamente, sem Crud.

Exemplo básico:

```php
// Model
#[Table(name: 'nome_da_tabela_do_banco')]
class MinhaClassePersonalizada extends \Szagot\Helper\Conn\Model\aModel
{
    #[PrimaryKey]
    private int     $id;
    private ?string $campo1;
    private ?string $campo2;
    
    #[IgnoreField]
    private OutraClasse $campoQueNaoPertenceATabela;
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

// Pegando um registro específico: ID = 1
try {
    /** @var MinhaClassePersonalizada $minhaInstancia */
    $minhaInstancia = Crud::get(MinhaClassePersonalizada::class, 1);
} catch (ConnException $e) {
    exit($e);
}
```

> **ATENÇÃO!** Se a chave primária não é do tipo de auto incremento, não esqueça de informar isso no atributo  
> PrimaryKey seguinte forma:

```php
// Model
#[Table(name: 'nome_da_tabela_do_banco')]
class MinhaClassePersonalizadaSemAutoIncremento extends \Szagot\Helper\Conn\Model\aModel
{
    #[PrimaryKey(autoIncrement: false)]
    private string  $code;
    private ?string $campo1;
    private ?string $campo2;
}
```

---

### Controle de Recebimento Requisições: `Szagot\Helper\Uri`

Em breve....

---

### Execução de Requisições: `Szagot\Helper\HttpRequest`

Em breve....