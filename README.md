# Instalação e Execução

## Requisitos

- Git
- Docker e Docker Compose

## 1. Download

```bash
git clone https://github.com/william-wv/ind-management.git
cd ind-management
```

## 2. Variáveis de ambiente

```bash
cp .env.example .env
```

## 3. Dependências

```bash
./run composer install
```

## 4. Subir os containers

```bash
./run up -d
```

## 5. Criar o banco e as tabelas

```bash
./run db:reset
```

## 6. Populate

```bash
./run db:populate
```

Cria um administrador e um usuário comum (ver dados de acesso abaixo).

## 7. Permissão da pasta de uploads

Necessário para o upload de avatar:

```bash
sudo chown -R www-data:www-data public/assets/uploads
```

## 8. Acessar

http://localhost:8081

## Dados de acesso

| Perfil | E-mail | Senha |
|---|---|---|
| `admin` | `fulano@example.com` | `123456` |
| `basic` | `fulano1@example.com` | `123456` |

## Testes e qualidade

```bash
./run test tests/Unit          # unitários
./run test tests/Integration   # integração e acesso às rotas
./run test:browser             # aceitação (Selenium)
./run phpcs                    # padrão de código (PSR-12)
./run phpstan                  # análise estática
```
