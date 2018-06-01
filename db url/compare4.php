-(void) loadingOverlayView
{
    // create a custom black view
    UIView *overlayView = [[UIView alloc] initWithFrame:self.navigationController.view.frame];
    overlayView.backgroundColor = [UIColor colorWithRed:256 green:256 blue:256 alpha:0];
    overlayView.tag = 88;
    
    indicator = [[UIActivityIndicatorView alloc] initWithActivityIndicatorStyle:UIActivityIndicatorViewStyleGray];
    indicator.frame = CGRectMake(self.view.bounds.size.width/2-indicator.frame.size.width/2,self.view.bounds.size.height/2-indicator.frame.size.height/2,indicator.frame.size.width,indicator.frame.size.height);
    indicator.tag = 77;
    [indicator startAnimating];
    
    
    // and just add them to navigationbar view
    [self.navigationController.view addSubview:overlayView];
    [self.navigationController.view addSubview:indicator];
}
