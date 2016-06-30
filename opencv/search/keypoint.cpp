#include <cv.h>
#include <highgui.h>
#include <iostream>
#include <fstream>

using namespace std;

const int DIM_VECTOR = 128;  // 128次元ベクトル



int main(int argc, char** argv) {
    const char* imageFile = argc == 3 ? argv[1] : "imgs/cellphone/image_0001.jpg";
/*    const char* surfFile  = argc == 3 ? argv[2] : "image/accordion_image_0001.surf";
*/
    // SURF抽出用に画像をグレースケールで読み込む
    IplImage* grayImage = cvLoadImage(imageFile, CV_LOAD_IMAGE_GRAYSCALE);
    if (!grayImage) {
        cerr << "cannot find image file: " << imageFile << endl;
        return -1;
    }

    CvMemStorage* storage = cvCreateMemStorage(0);
    CvSeq* imageKeypoints = 0;
    CvSeq* imageDescriptors = 0;
    CvSURFParams params = cvSURFParams(500, 1);

    // 画像からSURFを取得
    cvExtractSURF(grayImage, 0, &imageKeypoints, &imageDescriptors, storage, params);

    // key pointの情報見てみる
//    cout << "key point: " << imageKeypoints << endl;
    
    for (int i = 0; i < imageKeypoints->total; i++) {
        CvSURFPoint* point = (CvSURFPoint*)cvGetSeqElem(imageKeypoints, i);
//        cout << "key point[" << i << "]: " << point << endl;
        cout << "key point[" << i << "]: " << point->pt.x << ' ' << point->pt.y << ' ' << point->size << ' ' << point->laplacian << ' ' << endl;


     }



//   cvNamedWindow("SURF");

    // 後始末
    cvReleaseImage(&grayImage);
    cvClearSeq(imageDescriptors);
    cvReleaseMemStorage(&storage);
    cvDestroyAllWindows();

    return 0;
}
