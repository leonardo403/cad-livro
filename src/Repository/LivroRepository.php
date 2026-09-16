<?php

namespace App\Repository;

use App\Entity\Livro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livro>
 */
class LivroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livro::class);
    }

    /**
     * Retorna os dados do relatório a partir da view criada no banco.
     * Os livros vêm agrupados por autor (via tabela de ligação Livro_Autor).
     *
     * @return array<int, array<string, mixed>>
     */
    public function consultarRelatorio(): array
    {
        $sql = 'SELECT cod_livro, nome_autor, titulo, editora, edicao, ano_publicacao, valor
                FROM vw_relatorio_livros';

        return $this->getEntityManager()
            ->getConnection()
            ->fetchAllAssociative($sql);
    }
}