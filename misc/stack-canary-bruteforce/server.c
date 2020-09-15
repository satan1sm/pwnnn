#include <stdio.h>
#include <stdlib.h>
#include <netdb.h>
#include <netinet/in.h>
#include <string.h>
#include <unistd.h>
#include <netinet/tcp.h>
#include <sys/wait.h>

void serve(int sock)
{
        char buffer[32];

        // recv data
        recv(sock, buffer, 2048, 0);

        // send the message back
        send(sock, buffer, strlen(buffer), 0);
}

int main(int argc, char *argv[])
{
        int sockfd, newsockfd, portno, clilen;
        struct sockaddr_in serv_addr, cli_addr;
        int pid;

        sockfd = socket(AF_INET, SOCK_STREAM, 0);

        if (sockfd < 0) {
                perror("ERROR opening socket");
                exit(1);
        }

        if (setsockopt(sockfd, SOL_SOCKET, SO_REUSEADDR, &(int){1}, sizeof(int)) < 0) {
                perror("ERROR setsockopt");
                exit(1);
        }

        bzero((char *) &serv_addr, sizeof(serv_addr));
        portno = 1234;

        serv_addr.sin_family = AF_INET;
        serv_addr.sin_addr.s_addr = INADDR_ANY;
        serv_addr.sin_port = htons(portno);

        if (bind(sockfd, (struct sockaddr *) &serv_addr, sizeof(serv_addr)) < 0) {
                perror("ERROR on binding");
                exit(1);
        }

        listen(sockfd, 3);
        clilen = sizeof(cli_addr);

        for (;;) {
                newsockfd = accept(sockfd, (struct sockaddr *) &cli_addr, &clilen);

                if (newsockfd < 0) {
                        perror("ERROR on accept");
                        exit(1);
                }

                switch (pid = fork()) {
                case -1:
                        perror("ERROR on fork");
                        exit(1);
                        break;
                case 0:
                        close(sockfd);
                        write(newsockfd, "Welcome to the best echo service in the world!\n", 47);
                        serve(newsockfd);
                        send(newsockfd, "Goodbye!\n", strlen("Goodbye!\n"), 0);
                        close(newsockfd);
                        exit(0);
                        break;
                default:
                        close(newsockfd);
                        wait(NULL);
                        break;
                }
        }
}

