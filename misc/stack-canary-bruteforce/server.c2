#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <netinet/in.h>
#include <arpa/inet.h>

#define PORT 2223

void pwned(int socket)
{
    char data[64];
    bzero(data, sizeof(data));

    // overflow happens here
    recv(socket, data, 1024, 0);
    if (strlen(data))
    {
        printf("[*] Received: %s\n", data);
        send(socket, data, strlen(data), 0);
    }
}

void win()
{
    printf("EIP Overwrite Worked :)\n");
}

int main()
{
    int sockfd, ret;
    struct sockaddr_in serverAddr;

    int newSocket;
    struct sockaddr_in newAddr;

    socklen_t addr_size;

    char buffer[1024];
    pid_t childpid;

    sockfd = socket(AF_INET, SOCK_STREAM, 0);
    memset(&serverAddr, '\0', sizeof(serverAddr));
    serverAddr.sin_family = AF_INET;
    serverAddr.sin_port = htons(PORT);
    serverAddr.sin_addr.s_addr = inet_addr("127.0.0.1");

    ret = bind(sockfd, (struct sockaddr *)&serverAddr, sizeof(serverAddr));
    if (listen(sockfd, 10) == 0)
    {
        printf("[+] Listening....\n");
    }
    else {
        printf("[-] Error Binding to Port\n");
    }

    while (1)
    {
        newSocket = accept(sockfd, (struct sockaddr *)&newAddr, &addr_size);
        printf("[*] Got Connection from %s:%d\n", inet_ntoa(newAddr.sin_addr), ntohs(newAddr.sin_port));

        if ((childpid = fork()) == 0)
        {
            close(sockfd);

            pwned(newSocket);
            // if this works then no buffer overflow has occurred
            send(newSocket, "OK\n", strlen("OK\n"), 0);
        }
        else
        {
            close(newSocket);
        }
    }

    return 0;
}
