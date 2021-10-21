=========================
0. Intriduction

   This file will show you how to build the GPL linux system.
   
Step 1.	Install fedora linux 12 (choose Software Development) on 32bit CPU.

Step 2.	Setup Build Enviornment($means command)

	1) please login as a normal user such as john, and copy the gpl file to normal user folder,
	
	   such as the folder /home/john
	
	2) $cd /home/john
	
	3) $tar zxvf DIR859_Ax_GPL105b03.tar.gz
	
	4) $cd dir859_redo
	
	5) $su (ps : switch to root permission)
	
	6) #cp -rf mips-gcc-4.3.3-uClibc-0.9.30 /opt

	7) #rpm -ivh ./build_gpl/fakeroot-1.9.7-18.fc10.i386.rpm
		  
	8) #exit (ps : switch back to normal user permission)

	9) $source ./setupenv
	
Step 3. Building the image
	1) $make
	2) $make
	3) $make
	 ===================================================
	 You are going to build the f/w images.
	 Both the release and tftp images will be generated.
	 ===================================================
	 Do you want to build it now ? (yes/no) : yes
	 
	4) If there are some options need to be selected, please input "enter" key to execute the default action. 
	 
	5) After make successfully, you will find the image file in ./images/.
 
