## プロジェクト名
COACHTECH お問い合わせフォーム

## 概要
プロジェクトの目的と実装機能の概要説明

## ER図
```mermaid
erDiagram
    categories ||--o{ contacts : hasMany

    categories {
        bigint id PK
        varchar(255) content
        timestamp created_at
        timestamp updated_at
    }

    contacts {
        bigint id PK
        bigint category_id FK
        varchar(255) first_name
        varchar(255) last_name
        tinyint gender
        varchar(255) email
        varchar(11) tel
        varchar(255) address
        varchar(255) building
        varchar(120) detail
        timestamp created_at
        timestamp updated_at
    }

    tags {
        bigint id PK
        varchar(50) name
        timestamp created_at
        timestamp updated_at
    }

    contact_tag {
        bigint id PK
        bigint contact_id FK
        bigint tag_id FK
        timestamp created_at
        timestamp updated_at
    }

    contacts ||--o{ contact_tag : has
    tags ||--o{ contact_tag : has
```

## 環境構築手順

## 使用技術
- Laravel10.4
- MySQL 8.0
- Nginx
- Docker
- phpMyAdmin

## APIエンドポイント一覧

## 開発環境URL
http://localhost

## 作成者
浅井 明日香