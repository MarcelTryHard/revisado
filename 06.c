#include <stdio.h>

struct Aluno{
    char nome[50];
    int idade;
    float nota;
};

struct Livro{
    char titulo[50];
    char autor[50];
    int ano;
    float preco;
};

struct Contato{
    char nome[50];
    char telefone[20];
    char email[50];
};

struct Cliente{
    int conta;
    char nome[50];
    float saldo;
};

int main(){

    //================ EXERCÍCIO 1 =================

    struct Aluno aluno;

    printf("===== CADASTRO DE ALUNO =====\n");

    printf("Nome: ");
    scanf(" %[^\n]", aluno.nome);

    printf("Idade: ");
    scanf("%d", &aluno.idade);

    printf("Nota: ");
    scanf("%f", &aluno.nota);

    printf("\nAluno cadastrado:\n");
    printf("Nome: %s\n", aluno.nome);
    printf("Idade: %d\n", aluno.idade);
    printf("Nota: %.2f\n", aluno.nota);


    //================ EXERCÍCIO 2 =================

    struct Livro livros[5];

    printf("\n\n===== CADASTRO DE LIVROS =====\n");

    for(int i = 0; i < 5; i++){

        printf("\nLivro %d\n", i + 1);

        printf("Titulo: ");
        scanf(" %[^\n]", livros[i].titulo);

        printf("Autor: ");
        scanf(" %[^\n]", livros[i].autor);

        printf("Ano: ");
        scanf("%d", &livros[i].ano);

        printf("Preco: ");
        scanf("%f", &livros[i].preco);

    }

    printf("\nLivros cadastrados:\n");

    for(int i = 0; i < 5; i++){

        printf("\nLivro %d\n", i + 1);
        printf("Titulo: %s\n", livros[i].titulo);
        printf("Autor: %s\n", livros[i].autor);
        printf("Ano: %d\n", livros[i].ano);
        printf("Preco: %.2f\n", livros[i].preco);

    }


    //================ EXERCÍCIO 3 =================

    struct Contato contatos[10];
    int opcao;
    int qtd = 0;

    printf("\n\n===== AGENDA =====\n");

    do{

        printf("\n1 - Adicionar contato\n");
        printf("2 - Listar contatos\n");
        printf("3 - Sair\n");
        printf("Opcao: ");
        scanf("%d", &opcao);

        if(opcao == 1){

            printf("Nome: ");
            scanf(" %[^\n]", contatos[qtd].nome);

            printf("Telefone: ");
            scanf(" %[^\n]", contatos[qtd].telefone);

            printf("Email: ");
            scanf(" %[^\n]", contatos[qtd].email);

            qtd++;

            printf("Contato salvo!\n");

        }

        if(opcao == 2){

            if(qtd == 0){

                printf("Nenhum contato cadastrado.\n");

            }else{

                for(int i = 0; i < qtd; i++){

                    printf("\nContato %d\n", i + 1);
                    printf("Nome: %s\n", contatos[i].nome);
                    printf("Telefone: %s\n", contatos[i].telefone);
                    printf("Email: %s\n", contatos[i].email);

                }

            }

        }

    }while(opcao != 3);


    //================ EXERCÍCIO 4 =================

    struct Cliente cliente;
    float valor;

    printf("\n\n===== SISTEMA BANCARIO =====\n");

    printf("Numero da conta: ");
    scanf("%d", &cliente.conta);

    printf("Nome: ");
    scanf(" %[^\n]", cliente.nome);

    printf("Saldo inicial: ");
    scanf("%f", &cliente.saldo);

    printf("\nDigite um valor para depositar: ");
    scanf("%f", &valor);

    cliente.saldo = cliente.saldo + valor;

    printf("Saldo atual: %.2f\n", cliente.saldo);

    printf("\nDigite um valor para sacar: ");
    scanf("%f", &valor);

    if(valor <= cliente.saldo){

        cliente.saldo = cliente.saldo - valor;
        printf("Saque realizado.\n");

    }else{

        printf("Saldo insuficiente.\n");

    }

    printf("Saldo final: %.2f\n", cliente.saldo);

    return 0;
}