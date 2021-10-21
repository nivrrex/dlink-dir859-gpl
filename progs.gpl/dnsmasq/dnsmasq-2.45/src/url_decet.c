#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>

//#define PC_DEBUG 1

#ifdef PC_DEBUG
#define diag_printf printf
#else
#define diag_printf
#endif

#define MAX_URL_TYPE 50
#define MAX_URL_NUM 50


typedef struct url_type
{
	int type;
	char url[MAX_URL_NUM][50];
}url_type_t;

url_type_t url_info[50];


int set_url_type_to_kernel(int type,char *cookie,int len)
{
	int ret = 0;
	char cmdBuf[1024]="";
	int fd = open("/proc/dns_ip", O_WRONLY);

	sprintf(cmdBuf, "url_type=%d;cookie=\"%s\"", type, cookie);
	if (fd)
	{
		write(fd, cmdBuf, strlen(cmdBuf));
		close(fd);
		ret = 1;
	}

	diag_printf("%s\n",cmdBuf);

	return ret;
}

int set_dns_ip_to_kernel(int ip_type, char * ip_list)
{
	int ret = 0;
	char buf[1024];
	int fd = open("/proc/dns_ip", O_WRONLY);

	if (fd)
	{
		sprintf(buf, "ip_type=%d;len=1;%s", ip_type, ip_list);
		write(fd, buf, strlen(buf));
		close(fd);
		ret = 1;
	}

	return ret;
}

int store_url_info(int i,int type,int num,char *url,int len)
{
	if( (i < MAX_URL_TYPE) && ( num < MAX_URL_NUM) && ( len < 50 ))
	{
		url_info[i].type=type;
		memcpy(url_info[i].url[num],url,len);	
		return 1;
	}

	return 0;
}

int init_url_list_type(char *file)
{
	FILE *fp;
	char *p,*q;
	int ret = 0;

	char buf[512]="";
	int len=0,i=-1;

	int j=0;

	int type_num = 0;
	int url_type=0,num=0;
	int url_set_type=0;	

	memset(url_info,0,sizeof(url_info));

	fp = fopen(file, "r");

	if(!fp || (access(file, R_OK) == -1))
	{		
		return ret;
	}		

	while(1){
		memset(buf,0,sizeof(buf));

		if(!fgets(buf,sizeof(buf),fp))
			break;

		if(buf[0]=='#')
			continue;

		len=strlen(buf);

		if(len>0)
			buf[len-1]=0;

		if(buf[len-2]=='\r'||buf[len-2]=='\n')			
			buf[len-2]=0;

		if(memcmp(buf,"url_type",8)==0)
		{
			i++;
			num=0;
			p=buf+9;

			//p=strchr(p,'"');
			p=(char *)strchrnul(p,'"');

			if(p)	
			{
				p++;
				if(p)
					q=(char *)strchrnul(p,'"');

				if(q)
					*q=0;

				if(!p || !q || ((q-p) < 2) )
				{
					url_set_type = MAX_URL_TYPE;
					continue;
				}
			}

			url_type ++;
			url_set_type=url_type;

			set_url_type_to_kernel(url_type,p,strlen(p));

			continue;
		}

		if(buf[0]=='\t')
		{
			p=buf+1;
			//int store_url_info(int i,int type,int num,char *url,int len)
			diag_printf("i=%d url_type =%d num=%d str=%s\n",i,url_type,num,p);
			//store_url_info(i,url_type,num,p,strlen(p));
			store_url_info(i,url_set_type,num,p,strlen(p));
			num++;

			continue;
		}

		if(buf[0]!=0)
		{
			p=buf;
			//int store_url_info(int i,int type,int num,char *url,int len)
			diag_printf("i=%d url_type =%d  \tnum=%d len=%d \tstr=%s\n",i,url_type,num,strlen(p),p);
			//store_url_info(i,url_type,num,p,strlen(p));
			store_url_info(i,url_set_type,num,p,strlen(p));
			num++;
		}
	}

	diag_printf("================================================\n");

	fclose(fp);

	return 1;
}

int match_domain(char *data, int dlen, char *pattern, int plen, char term)
{	int i;	

	if(plen > dlen)	return 0;	

	if(pattern[0] == 0) return 0;	

	for(i=0; data[i] !=term ;i++)	{

		if(memcmp(data + i, pattern, plen)!=0)
			continue;	      
		else		
			return 1;	
	} 

	return 0;
}

int check_url(char *url, int url_len)
{

	int found=0;
	int i=0,j=0;

#if 0
	diag_printf("----------------------\n%s %d len\n",url,url_len);
	diag_printf("================================================\n");
	for(i=0; (i< MAX_URL_TYPE) && (url_info[i].type > 0); i++){
		for(j=0; j < MAX_URL_NUM; j++)
		{
			if((&url_info[i].url[j])[0]=='\0'||(strlen(url_info[i].url[j])==0) )
				break;

			diag_printf("i=%d j=%d  url = %s\n",i,j,url_info[i].url[j]);
		}
	}
	diag_printf("================================================\n");
#endif

	for(i=0; (i< MAX_URL_TYPE)&& (url_info[i].type > 0); i++){

		for(j=0; (j < MAX_URL_NUM); j++)
		{
			if((&url_info[i].url[j])[0]=='\0'||(strlen(url_info[i].url[j])==0) )
				break;

			diag_printf("i=%d j=%d len=%d  url =%s\n",i,j,strlen(url_info[i].url[j]),url_info[i].url[j]);

			found = match_domain(url,url_len, url_info[i].url[j], strlen(url_info[i].url[j]),'\0');
			if(found)
			{
				diag_printf("okey matched  current name type = %d\n",url_info[i].type);
				return url_info[i].type;
			}
		}
	}

	return 0;
}

#ifdef PC_DEBUG

#define URL1 "mediav.com"
int main(int argc, char *argv[]){

	int type=0;

	init_url_list_type();

	//type =check_url(&type,URL1,strlen(URL1));
	//diag_printf("type return is =%d\n",type);

	return;
}
#endif
